/*************************************************************************/
/*                                                                       */
/*  SpiderGL                                                             */
/*  JavaScript 3D Graphics Library on top of WebGL                       */
/*                                                                       */
/*  Copyright (C) 2010                                                   */
/*  Marco Di Benedetto                                                   */
/*  Visual Computing Laboratory                                          */
/*  ISTI - Italian National Research Council (CNR)                       */
/*  http://vcg.isti.cnr.it                                               */
/*  mailto: marco[DOT]dibenedetto[AT]isti[DOT]cnr[DOT]it                 */
/*                                                                       */
/*  This file is part of SpiderGL.                                       */
/*                                                                       */
/*  SpiderGL is free software; you can redistribute it and/or modify     */
/*  under the terms of the GNU Lesser General Public License as          */
/*  published by the Free Software Foundation; either version 2.1 of     */
/*  the License, or (at your option) any later version.                  */
/*                                                                       */
/*  SpiderGL is distributed in the hope that it will be useful, but      */
/*  WITHOUT ANY WARRANTY; without even the implied warranty of           */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 */
/*  See the GNU Lesser General Public License                            */
/*  (http://www.fsf.org/licensing/licenses/lgpl.html) for more details.  */
/*                                                                       */
/*************************************************************************/
/*
IfcWebViewer is part of IfcWebServer Project : http://code.google.com/p/ifcwebserver/
Copyright (C) 2011 Ali Ismail

change log:

22.06.2011:	 Show/Hide objects through checkboxes according to thier materials 
10.09.2011:	 Add the list of objects 
12.09.2011:	 Hide+Select filters 
*/

function loadXML(url, callback) {
	var async = (callback) ? (true) : (false);
	var req = new XMLHttpRequest();

	req.overrideMimeType("text/xml");

	if (async) {
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				if (callback) {
					callback(req.responseXML);
				}
			}
		};
	}

	req.open("GET", url, async);
	req.send(null);

	var ret = null;
	if (!async) {
		ret = req.responseXML;
	}

	return ret;
}

function getXmlElementById(xml, id) {
	if (!id) return null;

	var tags = xml.getElementsByTagName("*");
	var n = tags.length;

	for(var i=0; i<n; ++i) {
		if (tags[i].getAttribute("id") == id) {
			return tags[i];
		}
	}

	return null;
}

function getXmlElementBySId(xml, id) {
	if (!id) return null;

	var tags = xml.getElementsByTagName("*");
	var n = tags.length;

	for(var i=0; i<n; ++i) {
		if (tags[i].getAttribute("sid") == id) {
			return tags[i];
		}
	}

	return null;
}

function ColladaSource() {
	this.id     = null;
	this.size   = 0;
	this.stride = 0;
	this.offset = 0;
	this.buffer = [ ];
}

function ColladaInput() {
	this.semantic = null
	this.sourceID = null;
	this.offset   = -1;
	this.set      = -1;
}

function ColladaVertices() {
	this.id      = null;
	this.inputs  = [ ];
}

function ColladaTriangles() {
	this.inputs  = [ ];
	this.indices = [ ];
	this.materialSym = null;
}

function ColladaMesh() {
	this.sources   = { };
	this.triangles = [ ];
}

function ColladaGeometry() {
	this.id     = null;
	this.meshes = [ ];
}

function ColladaMaterial() {
	this.emissionCol  = [ 0.0, 0.0, 0.0, 0.0 ];
	this.ambientCol   = [ 0.1, 0.1, 0.1, 1.0 ];
	this.diffuseCol   = [ 1.0, 1.0, 1.0, 1.0 ];
	this.specularCol  = [ 0.0, 0.0, 0.0, 0.0 ];
	this.shininessVal = 0.0;

	this.emissionMap  = null;
	this.ambientMap   = null;
	this.diffuseMap   = null;
	this.specularMap  = null;
	this.shininessMap = null;
}

function ColladaInstanceGeometry() {
	this.materialID  = null;
	this.geometryID  = null;
	this.primitiveID = null;
}

function ColladaNode() {
	this.instanceGeometries = [ ];
	this.matrix             = sglIdentityM4();
	this.children           = [ ];
}

function ColladaInstanceVisualScene() {
	this.id    = null;
	this.nodes = [ ];
}

function ColladaScene() {
	this.instanceVisualSceneIDs = [ ];
}

function ColladaDocument() {
	this.geometries           = { };
	this.textures             = { };
	this.materials            = { };
	this.instanceVisualScenes = { };
	this.scenes               = { };
	this.hide_materials		= [];
	this.selected_nodes =[];
	this.hide_nodes = [];
	this.filter_select= "";
	this.filter_hide= "";
	
}

function getColladaArray(domRoot, domArray, collada, parseFunc) {
	if (!domRoot || !domArray || !collada) return null;

	if (!parseFunc) {
		parseFunc = function(value) { return value };
	}

	var domElem = domArray.firstChild;
	var prev = "";
	var output = [ ];
	var currentArray = null;

	while (domElem) {
		currentArray = (prev + domElem.nodeValue).replace(/\s+/g," ").replace(/^\s+/g,"").split(" ");
		domElem = domElem.nextSibling;

		if (currentArray[0] == "") {
			currentArray.unshift();
		}

		if (domElem) {
			prev = currentArray.pop();
		}

		for (var i=0, n=currentArray.length; i<n;i++) {
			output.push(parseFunc(currentArray[i]));
		}
	}

	return output;
}

function getXmlFirstTag(domNode, tagName) {
	var elems = domNode.getElementsByTagName(tagName);
	if (!elems) return null;
	if (elems.length <= 0) return null
	return elems[0];
}

function getColladaIntArray(domRoot, domArray, collada) {
	return getColladaArray(domRoot, domArray, collada, parseInt);
}

function getColladaFloatArray(domRoot, domArray, collada) {
	return getColladaArray(domRoot, domArray, collada, parseFloat);
}

function getColladaTexture(domRoot, domImage, collada) {
	if (!domRoot || !domImage || !collada) return null;

	var domInitFrom = getXmlFirstTag(domImage, "init_from");
	if (!domInitFrom) return null;

	var domTexFileName = domInitFrom.firstChild;
	if (!domTexFileName) return null;

	var texture = domTexFileName.data;
	if (!texture || (texture.length <= 0)) return null;

	return texture;
}

function getColladaTextures(domRoot, collada) {
	if (!domRoot || !collada) return null;

	for (var textureURL in collada.textures) {
		var texture = getColladaTexture(domRoot, getXmlElementById(domRoot, textureURL), collada);
		if (texture) {
			collada.textures[textureURL] = texture;
		}
		else {
			delete collada.textures[textureURL];
		}
	}
}

function getColladaTexturesID(domRoot, domTexture, collada) {
	if (!domRoot || !domTexture || !collada) return null;
}

function getColladaMaterialParam(domRoot, domEffect, domMaterialParam, collada) {
	var res = {
		textureID : null,
		value     : null
	};

	if (!domRoot || !domMaterialParam) return res;

	var domFloat = getXmlFirstTag(domMaterialParam, "float");
	if (domFloat) {
		res.value = getColladaFloatArray(domRoot, domFloat, collada);
		if (res.value) {
			if (res.value.length > 0) {
				res.value = [ res.value[0] ];
			}
		}
	}

	var domColor = getXmlFirstTag(domMaterialParam, "color");
	if (domColor) {
		res.value = getColladaFloatArray(domRoot, domColor, collada);
		if (res.value) {
			if (res.value.length == 3) {
				res.value.push(1.0);
			}
		}
	}

	var domTexture = getXmlFirstTag(domMaterialParam, "texture");
	if (domTexture) {
		var samplerSID = domTexture.getAttribute("texture");
		if (samplerSID) {
			var domSampler = getXmlElementBySId(domEffect, samplerSID);
			if (domSampler) {
				var domSource = getXmlFirstTag(domSampler, "source");
				if (domSource) {
					var surfaceSID = domSource.firstChild;
					if (surfaceSID) {
						var ssid = surfaceSID.data;
						var domSurface = getXmlElementBySId(domEffect, ssid);
						if (domSurface) {
							var domInitFrom = getXmlFirstTag(domSurface, "init_from");
							if (domInitFrom) {
								var textureID = domInitFrom.firstChild;
								if (textureID) {
									var tid = textureID.data;
									if (tid.length > 0) {
										res.textureID = tid;
										collada.textures[tid] = null;
									}
								}
							}
						}
					}
				}
			}
			else {
				if (samplerSID.length > 0) {
					res.textureID = samplerSID;
					collada.textures[samplerSID] = null;
				}
			}
		}
	}

	return res;
}

function getColladaMaterialTypeString(domCommonTechnique) {
	if (!domCommonTechnique) return null;

	var matType = null;
	var domElem = domCommonTechnique.firstChild;

	while (domElem && (!matType)) {
		switch (domElem.tagName) {
			case "lambert":
			case "blinn":
			case "phong":
				matType = domElem.tagName;
			break;

			default:
			break;
		}

		domElem = domElem.nextSibling;
	}

	return matType;
}

function getColladaMaterial(domRoot, domMaterial, collada) {
	if (!domRoot || !domMaterial || !collada) return null;

	var material = new ColladaMaterial();

	var domInstanceEffect = getXmlFirstTag(domMaterial, "instance_effect");
	if (!domInstanceEffect) return null;

	var effectURL = domInstanceEffect.getAttribute("url");
	if (!effectURL) return null;

	effectURL = effectURL.substr(1);
	if (effectURL.length <= 0) return null;

	var domEffect = getXmlElementById(domRoot, effectURL);
	if (!domEffect) return null;

	var domTechniques = domEffect.getElementsByTagName("technique");
	if (!domTechniques) return null;

	var domCommonTechnique = null;
	for (var i=0, n=domTechniques.length; i<n; ++i) {
		if (domTechniques[i].getAttribute("sid") == "common") {
			domCommonTechnique = domTechniques[i]
			break;
		}
	}
	if (!domCommonTechnique) return null;

	var domEmission   = getXmlFirstTag(domCommonTechnique, "emission");
	var emissionParam = getColladaMaterialParam(domRoot, domEffect, domEmission, collada);
	material.emissionCol = emissionParam.value;
	material.emissionMap = emissionParam.textureID;

	var domAmbient   = getXmlFirstTag(domCommonTechnique, "ambient");
	var ambientParam = getColladaMaterialParam(domRoot, domEffect, domAmbient, collada);
	material.ambientCol = ambientParam.value;
	material.ambientMap = ambientParam.textureID;

	var domDiffuse   = getXmlFirstTag(domCommonTechnique, "diffuse");
	var diffuseParam = getColladaMaterialParam(domRoot, domEffect, domDiffuse, collada);
	material.diffuseCol = diffuseParam.value;
	material.diffuseMap = diffuseParam.textureID;

	var domSpecular   = getXmlFirstTag(domCommonTechnique, "specular");
	var specularParam = getColladaMaterialParam(domRoot, domEffect, domSpecular, collada);
	material.specularCol = specularParam.value;
	material.specularMap = specularParam.textureID;

	var domShininess   = getXmlFirstTag(domCommonTechnique, "shininess");
	var shininessParam = getColladaMaterialParam(domRoot, domEffect, domShininess, collada);
	material.shininessVal = shininessParam.value;
	if (shininessParam.value) {
		if (shininessParam.value.length > 0) {
			material.shininessVal = shininessParam.value[0];
		}
	}
	material.shininessMap = shininessParam.textureID;

	var commonMaterialType = getColladaMaterialTypeString(domCommonTechnique);
	switch (commonMaterialType.tagName) {
		case "lambert":
			material.specularCol  = [ 0.0, 0.0, 0.0, 0.0 ];
			material.specularMap  = null;
			material.shininessVal = 0.0;
			material.shininessMap = null;
		break;

		case "blinn":
			material.shininessVal *= 128.0;
		break;

		case "phong":
		break;

		default:
		break;
	}
	material.id=domMaterial.getAttribute("id");
	material.name=domMaterial.getAttribute("name");
	
	return material;
}


function addSelectionMaterial (collada)
{
var material = new ColladaMaterial();
	material.id="select";
	material.name="select";	
	material.emissionCol  = [ 0.0, 0.0, 0.0, 0.0 ];
    material.ambientCol   = [ 0.1, 0.1, 0.1, 1.0 ];
    material.diffuseCol   = [ 1.0, 0.0, 0.0, 1.0 ];
    material.specularCol  = [ 0.0, 0.0, 0.0, 0.0 ];
    material.shininessVal = 0.0;
    material.emissionMap  = null;
    material.ambientMap   = null;
    material.diffuseMap   = null;
    material.specularMap  = null;
    material.shininessMap = null;
	collada.materials["select"] = material;
}


function getColladaMaterials(domRoot, collada) {
	if (!domRoot || !collada) return null;

	for (var materialURL in collada.materials) {
		var material = getColladaMaterial(domRoot, getXmlElementById(domRoot, materialURL), collada);
		if (material) {
			collada.materials[materialURL] = material;
		}
		else {
			delete collada.materials[materialURL];
		}
	}
}

function getColladaSource(domRoot, domSource, collada) {
	if (!domRoot || !domSource || !collada) return null;

	var source = new ColladaSource();
	source.id = domSource.getAttribute("id");
	var domTrechniqueCommon = domSource.getElementsByTagName("technique_common");
	if (!domTrechniqueCommon) return null;

	var domAccessors = domSource.getElementsByTagName("accessor");
	if (!domAccessors) return null;
	if (domAccessors.length <= 0) return null;

	var domAccessor = domAccessors[0];
	if (!domAccessor) return null;

	source.offset = domAccessor.getAttribute("offset");
	source.stride = domAccessor.getAttribute("stride");

	var domParams = domAccessor.getElementsByTagName("param");
	if (!domParams) return null;

	source.size   = domParams.length;

	var sourceArrayURL = domAccessor.getAttribute("source");
	if (!sourceArrayURL) return null;
	sourceArrayURL = sourceArrayURL.substr(1);
	if (sourceArrayURL.length <= 0) return null;

	var domArray = getXmlElementById(domSource, sourceArrayURL);
	if (!domArray) return null;

	source.buffer = getColladaFloatArray(domRoot, domArray, collada);

	return source;
}

function getColladaInput(domRoot, domInput, collada) {
	if (!domRoot || !domInput || !collada) return null;

	var input  = new ColladaInput();

	input.semantic = domInput.getAttribute("semantic");

	var str = domInput.getAttribute("source");
	if (str) {
		str = str.substr(1);
	}
	input.sourceID = ((str) ? (str) : (null));

	str = domInput.getAttribute("offset");
	input.offset = ((str) ? (parseInt(str)) : (-1));

	str = domInput.getAttribute("set");
	input.set = ((str) ? (parseInt(str)) : (-1));

	return input;
}

function getColladaVertices(domRoot, domVertices, collada) {
	if (!domRoot || !domVertices || !collada) return null;

	var vertices = new ColladaVertices();
	vertices.id = domVertices.getAttribute("id");

	var domElem = domVertices.firstChild;
	var input   = null;

	while (domElem) {
		switch (domElem.tagName) {
			case "input" :
				input = getColladaInput(domRoot, domElem, collada);
				if (input) {
					vertices.inputs.push(input);
				}
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	if (vertices.inputs.length <= 0) {
		return null;
	}

	return vertices;
}

function getColladaTriangles(domRoot, domTriangles, collada) {
	if (!domRoot || !domTriangles || !collada) return null;

	var triangles = new ColladaTriangles();

	triangles.materialSym = domTriangles.getAttribute("material");

	var domElem = domTriangles.firstChild;
	var input   = null;
	var indices = null;

	while (domElem) {
		switch (domElem.tagName) {
			case "input" :
				input = getColladaInput(domRoot, domElem, collada);
				if (input) {
					triangles.inputs.push(input);
				}
			break;

			case "p" :
				indices = getColladaIntArray(domRoot, domElem, collada);
				if (indices) {
					if (indices.length > 0) {
						triangles.indices = indices;
					}
				}
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	if ((triangles.inputs.length <= 0) || (triangles.indices.length <= 0)) {
		return null;
	}

	return triangles;
}

function getColladaPolylistAsTriangles(domRoot, domPolylist, collada) {
	if (!domRoot || !domPolylist || !collada) return null;

	var triangles = new ColladaTriangles();
	triangles.materialSym = domPolylist.getAttribute("material");
	var domElem = domPolylist.firstChild;
	var input   = null;
	var vcount  = null;
	var indices = null;

	while (domElem) {
		switch (domElem.tagName) {
			case "input" :
				input = getColladaInput(domRoot, domElem, collada);
				if (input) {
					triangles.inputs.push(input);
				}
			break;

			case "vcount" :
				vcount = getColladaIntArray(domRoot, domElem, collada);
			break;

			case "p" :
				indices = getColladaIntArray(domRoot, domElem, collada);
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	var inputsCount = triangles.inputs.length;
	if ((inputsCount <= 0) || !vcount || (vcount.length <=0) || !indices || (indices.length <= 0)) {
		return null;
	}	
	

	var maxInputOffset = -1;
	for (var j=0; j<inputsCount; ++j) {
		if (maxInputOffset < triangles.inputs[j].offset) {
			maxInputOffset = triangles.inputs[j].offset;
		}
	}
	if (maxInputOffset < 0) return null;

	var indexStride = maxInputOffset + 1;

	function readIndex(src, offset, cnt, dst) {
		for (var i=0; i<cnt; ++i) {
			dst[i] = src[offset + i];
		}
	}

	function appendIndex(dst, cnt, src) {
		for (var i=0; i<cnt; ++i) {
			dst.push(src[i]);
		}
	}

	var triIndices = [ ];
	var k = 0;
	var vc = 0;
	var i0 = new Array(indexStride);
	var i1 = new Array(indexStride);
	var i2 = new Array(indexStride);
	var iTmp = null;

	for (var v=0, vn=vcount.length; v<vn; ++v) {
		vc = vcount[v];
		if (vc < 3) continue;

		readIndex(indices, k, indexStride, i0); k += indexStride;
		readIndex(indices, k, indexStride, i1); k += indexStride;
		for (i=2; i<vc; ++i) {
			readIndex(indices, k, indexStride, i2); k += indexStride;
			appendIndex(triIndices, indexStride, i0);
			appendIndex(triIndices, indexStride, i1);
			appendIndex(triIndices, indexStride, i2);
			iTmp = i1;
			i1 = i2;
			i2 = iTmp;
		}
	}

	triangles.indices = triIndices;

	return triangles;
}

function getColladaMesh(domRoot, domMesh, collada) {
	if (!domRoot || !domMesh || !collada) return null;

	var mesh = new ColladaMesh();

	var domElem   = domMesh.firstChild;
	var source    = null;
	var sourcesCount = 0;
	var vertices  = null;
	var triangles = null;

	while (domElem) {
		switch (domElem.tagName) {
			case "source" :
				source = getColladaSource(domRoot, domElem, collada);
				if (source) {
					mesh.sources[source.id] = source;
					sourcesCount++;
				}
			break;

			case "vertices" :
				vertices = getColladaVertices(domRoot, domElem, collada);
			break;

			case "triangles" :
				triangles = getColladaTriangles(domRoot, domElem, collada);
				if (triangles) {
					mesh.triangles.push(triangles);
				}
			break;

			case "polylist" :
				triangles = getColladaPolylistAsTriangles(domRoot, domElem, collada);
				if (triangles) {
					mesh.triangles.push(triangles);
				}
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	var sortInputsByOffset = function(a, b) {
		return (a.offset - b.offset);
	}

	var vertInputCount = (vertices) ? (vertices.inputs.length) : (0);
	var numTriSets     = mesh.triangles.length;
	for (var i=0; i<numTriSets; ++i) {
		var triangles = mesh.triangles[i];
		var triInputsCount = triangles.inputs.length;

		var maxInputOffset = -1;
		for (var j=0; j<triInputsCount; ++j) {
			if (maxInputOffset < triangles.inputs[j].offset) {
				maxInputOffset = triangles.inputs[j].offset;
			}
		}
		if (maxInputOffset < 0) continue;
		var indexStride = maxInputOffset + 1;

		var newTriInputs = [ ];
		for (var j=0; j<triInputsCount; ++j) {
			var input = triangles.inputs[j];
			if ((vertInputCount > 0) && (input.sourceID == vertices.id)) {
				for (var k=0; k<vertInputCount; ++k) {
					var vertInput = vertices.inputs[k];
					vertInput.offset = input.offset;
					newTriInputs.push(vertInput);
				}
			}
			else {
				newTriInputs.push(input);
			}
		}

		newTriInputs.sort(sortInputsByOffset);

		var triInputsNewCount = newTriInputs.length;
		var newIndices = new Array(triInputsNewCount);
		for (var j=0; j<triInputsNewCount; ++j) {
			newIndices[j] = [ ];
		}

		var triIndicesCount = triangles.indices.length;
		for (var j=0; j<triInputsNewCount; ++j) {
			var input = newTriInputs[j];
			for (var k=input.offset; k<triIndicesCount; k+=indexStride) {
				newIndices[j].push(triangles.indices[k]);
			}
		}

		triangles.inputs  = newTriInputs;
		triangles.indices = newIndices;
	}

	return mesh;
}

function getColladaGeometry(domRoot, domGeometry, collada) {
	if (!domRoot || !domGeometry || !collada) return null;

	var geometry = new ColladaGeometry();
	geometry.id = domGeometry.getAttribute("id");
	//alert(geometry.id );
	if (!geometry.id) return null;

	var domElem = domGeometry.firstChild;
	var mesh = null;

	while (domElem) {
		switch (domElem.tagName) {
			case "mesh" :
				mesh = getColladaMesh(domRoot, domElem, collada);
				if (mesh) {
					geometry.meshes.push(mesh);
				}
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	if (geometry.meshes.length <= 0) {
		return null;
	}

	return geometry;
}

function getColladaGeometries(domRoot, collada) {
	if (!domRoot || !collada) return null;

	for (var geometryURL in collada.geometries) {
		var geometry = getColladaGeometry(domRoot, getXmlElementById(domRoot, geometryURL), collada);
		if (geometry) {
			collada.geometries[geometryURL] = geometry;
		}
		else {
			delete collada.geometries[geometryURL];
		}
	}
}

function getColladaInstanceGeometry(domRoot, domInstanceGeometry, collada) {
	if (!domRoot || !domInstanceGeometry || !collada) return null;

	var geometryURL = domInstanceGeometry.getAttribute("url");
	if (!geometryURL) return null;

	geometryURL = geometryURL.substr(1);
	if (geometryURL.length <= 0) return null;

	var instanceGeometries = [ ];

	collada.geometries[geometryURL] = null;

	var domInstanceMaterials = domInstanceGeometry.getElementsByTagName("instance_material");
	
	for (var i=0, n=domInstanceMaterials.length; i<n; ++i) {
		var domInstanceMaterial = domInstanceMaterials[i];
		if (!domInstanceMaterial) continue;		
	// ALI:Add code to exclude geometry elements depending on the materials
	for(var j=0,hm=collada.hide_materials.length;j<hm;++j){
		if (domInstanceMaterial.getAttribute("target") == collada.hide_materials[j]) return null;
	}
	//END_ALI
		
		var instanceGeometry = new ColladaInstanceGeometry();
		instanceGeometry.geometryID = geometryURL;

		var materialURL = domInstanceMaterial.getAttribute("target");		
		// ALI:Add code to exclude geometry elements depending on the materials
		for( var j=0,hm=collada.selected_nodes.length;j<hm;++j){
			if (instanceGeometry.geometryID == collada.selected_nodes[j])  materialURL = "#selected";
		}
		
		if (collada.filter_select != ""){
		var SelectArray =collada.filter_select.split("+");	
		for ( i=0; i < SelectArray.length; ++i ){
		if ( instanceGeometry.geometryID.indexOf(SelectArray[i]) != -1)			
			materialURL = "#selected";
			break;
		}
		}		
		//END_ALI	
		if (materialURL) {
			materialURL = materialURL.substr(1);
			if (materialURL.length > 0) {
				instanceGeometry.materialID = materialURL;
				collada.materials[materialURL] = null;
			}
		}
		var materialSym = domInstanceMaterial.getAttribute("symbol");
		
		if (materialSym.length > 0) {
			instanceGeometry.primitiveID = materialSym;
		}

		instanceGeometries.push(instanceGeometry);
	}

	return instanceGeometries;
}

function getColladaNode(domRoot, domNode, collada) {
	if (!domRoot || !domNode || !collada) return null;
	//ALI:add list of node objects
	//document.getElementById("Nodes").innerHTML += (domNode.getAttribute("id") + "<br>");
	if (domNode.getAttribute("id"))
	{
	document.getElementById("Nodes").options[document.getElementById("Nodes").length] =new Option(domNode.getAttribute("id"), domNode.getAttribute("id"));
	for(var i=0,hm=collada.hide_nodes.length;i<hm;++i){
		if (domNode.getAttribute("id") == collada.hide_nodes[i]) return null;
	}
	if (collada.filter_hide != ""){
	var HideArray =collada.filter_hide.split("+");	
	for ( i=0; i < HideArray.length; ++i ){
	 if ( domNode.getAttribute("id").indexOf(HideArray[i]) != -1)
		return null; 
	}
	}	
	}	
	//END_ALI
	var domElem  = domNode.firstChild;

	var node      = new ColladaNode();
	var matrix    = sglIdentityM4();
	var childNode = null;
	var instanceGeometries = null;
	var arr = null;
	
	while (domElem) {
		var nextElem = domElem.nextSibling;
		switch (domElem.tagName) {
			case "node" :
				childNode = getColladaNode(domRoot, domElem, collada);
				if (childNode) {
					node.children.push(childNode);
				}
			break;

			case "translate" :
				arr = getColladaFloatArray(domRoot, domElem, collada);
				if (arr) {
					if (arr.length == 3) {
						matrix = sglMulM4(matrix, sglTranslationM4V(arr));
					}
				}
			break;
			case "rotate" :
				arr = getColladaFloatArray(domRoot, domElem, collada);
				if (arr) {
					if (arr.length == 4) {
						matrix = sglMulM4(matrix, sglRotationAngleAxisM4V(sglDegToRad(arr[3]), arr.slice(0, 3)));
					}
				}
			break;

			case "scale" :
				arr = getColladaFloatArray(domRoot, domElem, collada);
				if (arr) {
					if (arr.length == 3) {
						matrix = sglMulM4(matrix, sglScalingM4V(arr));
					}
				}
			break;

			case "instance_geometry" :
				instanceGeometries = getColladaInstanceGeometry(domRoot, domElem, collada);
				//ALI
				//if ( instanceGeometries[0].materialID.indexOf("Yellow") != -1)
					//return null;
				
				if (instanceGeometries) {
					node.instanceGeometries = node.instanceGeometries.concat(instanceGeometries);
				}
				
			break;

			default :
			break;
		}

		domElem = domElem.nextSibling;
	}

	node.matrix = matrix;

	if ((node.children.length <= 0) && (node.instanceGeometries.length <= 0)) {
		return null;
	}

	return node;
}

function getColladaInstanceVisualScene(domRoot, domInstanceVisualScene, collada) {
	if (!domRoot || !domInstanceVisualScene || !collada) return null;

	var domNodes = [ ];
	var domElem = domInstanceVisualScene.firstChild;
	while (domElem) {
		if (domElem.tagName == "node") {
			domNodes.push(domElem);
		}
		domElem = domElem.nextSibling;
	}

	var domNodesCount = domNodes.length;
	if (domNodesCount <= 0) return null;

	var instanceVisualScene = new ColladaInstanceVisualScene();
	instanceVisualScene.id = domInstanceVisualScene.getAttribute("id");
	if (!instanceVisualScene.id) return null;

	for (var i=0; i<domNodesCount; ++i) {
		var domNode = domNodes[i];
		if (!domNode) continue;

		var node = getColladaNode(domRoot, domNode, collada);
		if (!node) continue;

		instanceVisualScene.nodes.push(node);
	}

	if (instanceVisualScene.nodes.length <= 0) {
		return null;
	}

	return instanceVisualScene;
}

function getColladaInstanceVisualScenes(domRoot, collada) {
	if (!domRoot || !collada) return null;

	for (var instanceVisualSceneURL in collada.instanceVisualScenes) {
		var instanceVisualScene = getColladaInstanceVisualScene(domRoot, getXmlElementById(domRoot, instanceVisualSceneURL), collada);
		if (instanceVisualScene) {
			collada.instanceVisualScenes[instanceVisualSceneURL] = instanceVisualScene;
		}
		else {
			delete collada.instanceVisualScenes[instanceVisualSceneURL];
		}
	}
}

function getColladaScene(domRoot, domScene, collada) {
	if (!domRoot || !domScene || !collada) return null;

	var domInstanceVisualScenes = domScene.getElementsByTagName("instance_visual_scene");
	if (!domInstanceVisualScenes) return null;
	var domInstanceVisualScenesCount = domInstanceVisualScenes.length;
	if (domInstanceVisualScenesCount <= 0) return null;

	var scene = new ColladaScene();

	for (var i=0; i<domInstanceVisualScenesCount; ++i) {
		var domInstanceVisualScene = domInstanceVisualScenes[i];
		if (!domInstanceVisualScene) continue;

		var instanceVisualSceneURL = domInstanceVisualScene.getAttribute("url");
		if (!instanceVisualSceneURL) continue;

		instanceVisualSceneURL = instanceVisualSceneURL.substr(1);
		if (instanceVisualSceneURL.length <= 0) continue;

		collada.instanceVisualScenes[instanceVisualSceneURL] = null;
		scene.instanceVisualSceneIDs.push(instanceVisualSceneURL);
	}

	if (scene.instanceVisualSceneIDs.length <= 0) {
		return null;
	}

	return scene;
}

function getColladaScenes(domRoot, collada) {
	if (!domRoot || !collada) return;

	var domScenes = domRoot.getElementsByTagName("scene");
	if (!domScenes) return;
	var domScenesCount = domScenes.length;
	if (domScenesCount <= 0) return;

	collada.scenes = { };
	var k = 0;

	for (var i=0; i<domScenesCount; ++i) {
		var domScene = domScenes[i];
		if (!domScene) continue;

		var scene = getColladaScene(domRoot, domScene, collada);
		if (!scene) continue;

		collada.scenes["scene_" + k] = scene;
		k++;
	}
}

function getCollada(domRoot) {
	if (!domRoot) return null;
	
	var collada = new ColladaDocument();
	//ALI: Read Filter_hide & Filter_select
	collada.filter_hide=document.getElementById("filter_hide").value;
	collada.filter_select=document.getElementById("filter_select").value;	
	//END_ALI
	getColladaScenes                (domRoot, collada);
	getColladaInstanceVisualScenes  (domRoot, collada);
	getColladaGeometries            (domRoot, collada);
	addSelectionMaterial			(collada);	
	getColladaMaterials             (domRoot, collada);
	getColladaTextures              (domRoot, collada);
	return collada;
}
