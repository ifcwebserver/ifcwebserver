<?php
/* IfcWebViewer is part of IfcWebServer Project 
http://code.google.com/p/ifcwebserver/
Copyright (C) 2011 Ali Ismail

SpiderGL WebGL Library :
 Copyright (C) 2010                                                   
 Marco Di Benedetto                                                   
  Visual Computing Laboratory                                         
  ISTI - Italian National Research Council (CNR)                       
  http://vcg.isti.cnr.it   
/*************************************************************************/					
/*  IfcWebViewer is free software; you can redistribute it and/or modify */
/*  under the terms of the GNU Lesser General Public License as          */
/*  published by the Free Software Foundation; either version 2.1 of     */
/*  the License, or (at your option) any later version.                  */
/*                                                                       */
/*  IfcWebViewer is distributed in the hope that it will be useful, but      */
/*  WITHOUT ANY WARRANTY; without even the implied warranty of           */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                 */
/*  See the GNU Lesser General Public License                            */
/*  (http://www.fsf.org/licensing/licenses/lgpl.html) for more details.  */
/*                                                                       */
/*************************************************************************/
/* change log:
22.06.2011:	 Show/Hide objects through checkboxes according to thier materials 
10.09.2011:	 Add the list of objects 
12.09.2011:	 Hide+Select filters 
*/
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"> 
 <head> 
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />  
  <meta HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"> 
  <meta HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE"> 
  <title>IfcWebViewer</title> 
  <!-- Liquid Blueprint CSS --> 
  <link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection"> 
  <link rel="stylesheet" href="css/blueprint/print.css" type="text/css" media="print">	
  <!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]--> 
   <link rel="stylesheet" href="css/style.css" type="text/css" media="screen, projection"/>  
</head> 
<?php
$url ="";
$bgColor="0.92 0.92 0.92";
$url=$_GET["url"];
$bgColor=$_GET["bgcolor"];
$filter_hide=$_GET["filter_hide"];
$filter_select=$_GET["filter_select"];
if ($_GET["bgcolor"] != null)
{
$bgColor=$_GET["bgcolor"];
}
else
{
$bgColor="0.92,0.92,0.92";
}
if ($url != "") {
echo "<SCRIPT LANGUAGE=\"javascript\">\n";
//echo "<!--\n";
echo "var scenes = [{ url:$url,specular:[0.0, 0.0, 0.0, 0.0], light:true,  eye:[0.0, -2.0, 2.0]  }];\n";
echo "var daeSource = scenes[0];\n";
echo " gColladaTest.loadDataset(daeSource);\n";
echo "</SCRIPT>\n";
}
else
{
$url ="http://bci52.cib.bau.tu-dresden.de/dae/AC10-Institute-Var-1.dae";
}
?>
<form name="url_form">
<input hidden type="text" name="url" value="<?php echo $url ?>">
</form>
 <body id="index">   
    <script type="text/javascript" src="js/spidergl.js"></script> 
	<script type="text/javascript" src="js/collada.js"></script> 
	<script id="PHONG_VERTEX_SHADER" type="x-shader/x-vertex"> 
#ifdef GL_ES
precision highp float;
#endif

uniform   mat4 u_model_view_projection_mat;
uniform   mat4 u_model_view_mat;
uniform   mat3 u_view_normal_mat;
 
attribute vec4 a_position;
attribute vec3 a_normal;
attribute vec2 a_texcoord;
 
varying   vec3 v_view_position;
varying   vec3 v_view_normal;
varying   vec2 v_texcoord;
 
void main(void)
{
 
	v_view_position = (u_model_view_mat  * a_position).xyz;
	v_view_normal   = u_view_normal_mat * a_normal;
	v_texcoord      = a_texcoord;
 
	gl_Position = u_model_view_projection_mat * a_position;
}
</script> 
 
<script id="PHONG_FRAGMENT_SHADER" type="x-shader/x-fragment"> 
#ifdef GL_ES
precision highp float;
#endif
 
uniform vec4      u_view_light_dir;
 
uniform vec4      u_emission;
uniform vec4      u_ambient;
uniform vec4      u_diffuse;
uniform vec4      u_specular;
uniform float     u_shininess;
 
uniform sampler2D s_emission;
uniform sampler2D s_ambient;
uniform sampler2D s_diffuse;
uniform sampler2D s_specular;
uniform sampler2D s_shininess;
 
varying vec3      v_view_position;
varying vec3      v_view_normal;
varying vec2      v_texcoord;
 
void main(void)
{
	vec3  matEmission  = u_emission.xyz + texture2D(s_emission, v_texcoord).xyz;
	vec3  matAmbient   = u_ambient.xyz;
	vec3  matDiffuse   = u_diffuse.xyz * texture2D(s_diffuse, v_texcoord).xyz;
	vec3  matAccessibility = texture2D(s_ambient, v_texcoord).xyz;
	vec3  matSpecular  = u_specular.xyz * texture2D(s_specular, v_texcoord).xyz;
	float matShininess = u_shininess + texture2D(s_shininess, v_texcoord).x;
 
	vec3  view_normal  = normalize(v_view_normal);
	float n_dot_l      = abs(dot(view_normal, -u_view_light_dir.xyz));
	if (u_view_light_dir.w > 0.5) n_dot_l = 1.0;
	vec3  refl_vector  = reflect(u_view_light_dir.xyz, view_normal);
	vec3  view_vector  = normalize(-v_view_position);
 
	vec3  emission = matEmission;
	vec3  ambient  = matAmbient * matDiffuse;
	vec3  diffuse  = matDiffuse * n_dot_l * matAccessibility;
	vec3  specular = matSpecular * pow(max(dot(refl_vector, view_vector), 0.0), u_shininess);
 
	vec3  color = emission + ambient + diffuse + specular;
 
	gl_FragData[0] = vec4(color, 1.0);
}
</script> 
 
<script type="text/javascript"> 
function log(msg) {
	document.getElementById("LOG").innerHTML += (msg + "\n");
}
 
function flattenIndices(names, sizes, values, ariety, indices) {
	var attribsCount = sizes.length;
 
	var res = {
		names   : new Array(attribsCount),
		sizes   : new Array(attribsCount),
		values  : new Array(attribsCount),
		ariety  : ariety,
		indices : [ ]
	};
 
	for (var a=0; a<attribsCount; ++a) {
		res.names[a]  = names[a];
		res.sizes[a]  = sizes[a];
		res.values[a] = [ ];
	}
 
	var indicesCount  = indices[0].length;
	var verticesCount = 0;
 
	var remap    = { };
	var idxs     = new Array(attribsCount);
 
	for (var i=0; i<indicesCount; ++i) {
		var v = indices[0][i];
		idxs[0] = v;
		var vertexString = "" + v;
		for (var a=1; a<attribsCount; ++a) {
			v = indices[a][i];
			idxs[a] = v;
			vertexString += "_" + v;
		}
		
 
		var vertexIndex = remap[vertexString];
		if (vertexIndex == undefined) {
			for (var a=0; a<attribsCount; ++a) {
				var src   = values[a];
				var dst   = res.values[a];
				var size  = res.sizes[a];
				var begin = idxs[a] * size;
				var end   = begin + size;
 
				for (var c=begin; c<end; ++c) {
					dst.push(src[c]);
				}
			}
 
			vertexIndex = verticesCount;
			remap[vertexString] = vertexIndex;
			verticesCount++;
		}
 
		res.indices.push(vertexIndex);
	}
 
	return res;
}
 
function colladaMeshToMeshJSArray(colladaMesh) {
	var meshesJS = [ ];
 
	for (var t in colladaMesh.triangles) {
		var triangles = colladaMesh.triangles[t];
 
		var names   = [ ];
		var sizes   = [ ];
		var values  = [ ];
		var indices = [ ];
 
		for (var i in triangles.inputs) {
			var input  = triangles.inputs[i];
			var source = colladaMesh.sources[input.sourceID];
			var sName  = input.semantic.toLowerCase();
			if (source.set >= 0) {
				sName += source.set;
			}
			names.push(sName);
			sizes.push(source.size);
			values.push(source.buffer);
		}
 
		var ariety  = 3;
		var indices = triangles.indices;
 
		var flatMesh = flattenIndices(names, sizes, values, ariety, indices);
 
		var meshJS = new SglMeshJS();
 
		var attribsCount = flatMesh.names.length;
		for (var a=0; a<attribsCount; ++a) {
			meshJS.addVertexAttribute(flatMesh.names[a], flatMesh.sizes[a], flatMesh.values[a]);
		}
		var primName = triangles.materialSym || "triangles";
		meshJS.addIndexedPrimitives(primName, SGL_TRIANGLES_LIST, flatMesh.indices);
 
		meshesJS.push(meshJS);
	}
 
	return meshesJS;
}
 
function GLMaterial(blackTexName, whiteTexName) {
	this.emissionCol  = [ 0.0, 0.0, 0.0, 0.0 ];
	this.ambientCol   = [ 0.1, 0.1, 0.1, 1.0 ];
	this.diffuseCol   = [ 1.0, 1.0, 1.0, 1.0 ];
	this.specularCol  = [ 0.0, 0.0, 0.0, 0.0 ];
	this.shininessVal = 0.0;
 
	this.emissionMap  = blackTexName;
	this.ambientMap   = whiteTexName;
	this.diffuseMap   = whiteTexName;
	this.specularMap  = whiteTexName;
	this.shininessMap = blackTexName;
}
 
function GLNode() {
	this.meshGroupIDs = [ ];
	this.matrix       = sglIdentityM4();
	this.children     = [ ];
}
 
function GLInstanceVisualScene() {
	this.id    = null;
	this.nodes = [ ];
}
 
function GLScene() {
	this.instanceVisualSceneIDs = [ ];
}
 
function GLDocument() {
	this.textures             = { };
	this.materials            = { };
	this.meshGroups           = { };
	this.instanceVisualScenes = { };
	this.scenes               = { };
}
 
function colladaNodeToGL(node) {
	if (!node) return null;
	
	var gn = new GLNode();
 
	gn.meshGroupIDs = node.instanceGeometries;
	gn.matrix = node.matrix;
	for (var i=0, n=node.children.length; i<n; ++i) {
		var child = colladaNodeToGL(node.children[i]);
		
		if (!child) continue;
		gn.children.push(child);
	}
 
	return gn;
}
 
function getGLMeshBBox(mesh, matrix, doc) {
	return mesh.bbox.transformed(matrix);
}
 
function getGLMeshArrayBBox(meshArray, matrix, doc) {
	var bbox = new SglBox3();
	for (var m in meshArray) {
		var mesh = meshArray[m];
		bbox.addBox(getGLMeshBBox(mesh, matrix, doc));
	}
	return bbox;	
}
 
function getGLNodeBBox(node, matrix, doc) {
	var mat = sglMulM4(matrix, node.matrix);
	var bbox = new SglBox3();
	for (var c in node.children) {
		var child = node.children[c];
		bbox.addBox(getGLNodeBBox(child, mat, doc));
	}
	for (var m in node.meshGroupIDs) {
		var meshArray = doc.meshGroups[node.meshGroupIDs[m].geometryID];
		bbox.addBox(getGLMeshArrayBBox(meshArray, mat, doc));
	}
	return bbox;	
}
 
function getGLVisualSceneBBox(visualScene, matrix, doc) {
	var bbox = new SglBox3();
	for (var n in visualScene.nodes) {
		var node = visualScene.nodes[n];
		bbox.addBox(getGLNodeBBox(node, matrix, doc));
	}
	return bbox;	
}
 
function getGLSceneBBox(scene, matrix, doc) {
	var bbox = new SglBox3();
	for (var v in scene.instanceVisualSceneIDs) {
		var visualScene = doc.instanceVisualScenes[scene.instanceVisualSceneIDs[v]];
		bbox.addBox(getGLVisualSceneBBox(visualScene, matrix, doc));
	}
	return bbox;	
}
 
function getGLDocumentBBox(doc) {
	var matrix = sglIdentityM4();
	var bbox   = new SglBox3();
	for (var s in doc.scenes) {
		var scene = doc.scenes[s];
		bbox.addBox(getGLSceneBBox(scene, matrix, doc));
	}
	return bbox;
}
 
function getGLMaterial(gl, blackTexName, whiteTexName, material) {
	var res = new GLMaterial(blackTexName, whiteTexName);
 
	if (material.emissionCol) {
		res.emissionCol = material.emissionCol;
	}
	if (material.emissionMap) {
		res.emissionCol = [ 0.0, 0.0, 0.0, 0.0 ];
		res.emissionMap = material.emissionMap;
	}
 
	if (material.ambientCol) {
		res.ambientCol = material.ambientCol;
	}
	if (material.ambientMap) {
		//res.ambientCol = [ 0.0, 0.0, 0.0, 0.0 ];
		res.ambientMap = material.ambientMap;
	}
 
	if (material.diffuseCol) {
		res.diffuseCol = material.diffuseCol;
	}
	if (material.diffuseMap) {
		//res.diffuseCol = [ 1.0, 1.0, 1.0, 1.0 ];
		res.diffuseMap = material.diffuseMap;
	}
 
	if (material.specularCol) {
		res.specularCol = material.specularCol;
	}
	if (material.specularMap) {
		res.specularCol = [ 1.0, 1.0, 1.0, 1.0 ];
		res.specularMap = material.specularMap;
	}
 
	if (material.shininessVal) {
		res.shininessVal = material.shininessVal;
	}
	if (material.shininessMap) {
		res.shininessVal = [ 0.0 ];
		res.shininessMap = material.shininessMap;
	}
 
	return res;
}
 
//ALI start 
function rgbToHex(R,G,B) {return toHex(R)+toHex(G)+toHex(B)}
function toHex(n) {
 n = parseInt(n,10);
 if (isNaN(n)) return "00";
 n = Math.max(0,Math.min(n,255));
 return "0123456789ABCDEF".charAt((n-n%16)/16)
      + "0123456789ABCDEF".charAt(n%16);
}
//ALi end
 
function colladaToGLDocument(gl, collada, colladaBasePath, daeSource) {
	if (!gl || !collada) return null;
	
	var gldoc = new GLDocument();
 
	var texOpts = {
		minFilter : gl.LINEAR_MIPMAP_LINEAR,
		magFilter : gl.LINEAR,
		wrapS     : gl.REPEAT,
		wrapT     : gl.REPEAT,
		generateMipmap : true
	};
 
	for (var t in collada.textures) {
		var texFile = colladaBasePath + "/" + collada.textures[t];
		gldoc.textures[t] = new SglTexture2D(gl, texFile, texOpts);
	}
 
	var dummyBlackTexels = new Uint8Array([
		0, 0, 0, 0
	]);
	var dummyBlackTex = new SglTexture2D(gl, gl.RGBA, 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, dummyBlackTexels, texOpts);
	var dummyBlackTexName = "dummyBlack";
	while (gldoc.textures[dummyBlackTexName]) {
		dummyBlackTexName += "_";
	}
	gldoc.textures[dummyBlackTexName] = dummyBlackTex;
	gldoc.blackTex = dummyBlackTex;
 
	var dummyWhiteTexels = new Uint8Array([
		255, 255, 255, 255
	]);
	var dummyWhiteTex = new SglTexture2D(gl, gl.RGBA, 1, 1, gl.RGBA, gl.UNSIGNED_BYTE, dummyWhiteTexels, texOpts);
	var dummyWhiteTexName = "dummyWhite";
	while (gldoc.textures[dummyWhiteTexName]) {
		dummyWhiteTexName += "_";
	}
	gldoc.textures[dummyWhiteTexName] = dummyWhiteTex;
	gldoc.whiteTex = dummyWhiteTex;
	document.getElementById("LayersByMat").innerHTML = "Show/Hide by Material/Class:"; //ALI
	for (var m in collada.materials) {
		var material = collada.materials[m];	
		var hexColor=""
		if(material.diffuseCol!= null) {
		 hexColor = rgbToHex(material.diffuseCol[0]*255,material.diffuseCol[1]*255,material.diffuseCol[2]*255);//ALI
		 }
		document.getElementById("LayersByMat").innerHTML += ("<input id='" + material.id + "' type='checkbox'  name='" + material.id + "'  checked onchange=\"toggleMaterial('" + material.id + "')\"><font  size=\"5\" color=\"#" + hexColor + "\">&#9830;</font>" +  material.name + " ") ; //+"<div style='height:25px;width:25px;background-color:#" + hexColor +   "'> </div>");//ALI
		
		var glMat = getGLMaterial(gl, dummyBlackTexName, dummyWhiteTexName, material);
		if (daeSource.specular) {
			glMat.specularCol = daeSource.specular.slice();
		}
		gldoc.materials[m] = glMat;
	}
 
	for (var g in collada.geometries) {
		var geometry = collada.geometries[g];
		var meshesGL = [ ];
		for (var m in geometry.meshes) {
			var mesh = geometry.meshes[m];
			var meshesJS = colladaMeshToMeshJSArray(mesh);
			for (var mj in meshesJS) {
				var meshJS = meshesJS[mj];
				var meshGL = sglMeshJStoGL(gl, meshJS);
				meshGL.bbox = sglMeshJSCalculateBBox(meshJS);
				meshesGL.push(meshGL);
			}
		}
		gldoc.meshGroups[g] = meshesGL;
	}
 
	for (var v in collada.instanceVisualScenes) {
		var instanceVisualScenes = collada.instanceVisualScenes[v];
		var gv = new GLInstanceVisualScene();
		gv.id = instanceVisualScenes.id;
		for (var i=0, n=instanceVisualScenes.nodes.length; i<n; ++i) {
			var node = instanceVisualScenes.nodes[i];			
			gv.nodes.push(colladaNodeToGL(node));
		}
		gldoc.instanceVisualScenes[v] = gv;
	}
 
	for (var s in collada.scenes) {
		var scene = collada.scenes[s];
		var gs = new GLScene();
		gs.instanceVisualSceneIDs = scene.instanceVisualSceneIDs.slice();
		gldoc.scenes[s] = gs;
	}
 
	gldoc.bbox = getGLDocumentBBox(gldoc);
	gldoc.sceneInfo = daeSource;
 
	return gldoc;
}
 
function ColladaTest() {
	;
}
 
ColladaTest.prototype = {
	loadDataset : function(daeSource) {
		this.gldoc = null;
 
		var that = this;
		var daePath = daeSource.url.substr(0, daeSource.url.lastIndexOf("/"));
 
		function onloadDataset(domCollada) {		
			var collada = getCollada(domCollada);			
			that.gldoc  = colladaToGLDocument(that.ui.gl, collada, daePath, daeSource);
			that.trackball.reset();
			var eye = that.gldoc.sceneInfo.eye;
			that.camera.lookAt(eye[0], eye[1], eye[2], 0.0, 0.0, 0.0, sglDegToRad(0.0));
			
			
			that.viewMatrix = that.camera.matrix;
			that.ui.requestDraw();
		}
 
		loadXML(daeSource.url, onloadDataset);
	},
 
	load : function(gl) {
		
 
		/*************************************************************/
		this.xform      = new SglTransformStack();
		this.trackball  = new SglTrackball();
		this.camera     = new SglFirstPersonCamera();
		//var cpos        = sglMulSV3(3.0, sglNormalizedV3([1.0, 2.5, 3.0]));
		//this.camera.lookAt(cpos[0], cpos[1], cpos[2], -0.5, 0.0, 0.0, sglDegToRad(0.0));
		this.camera.lookAt(0.0, 0.0, 2.0, 0.0, 0.0, 0.0, sglDegToRad(0.0));
		this.viewMatrix = this.camera.matrix;
		/*************************************************************/
 
 
		/*************************************************************/
		this.prog = new SglProgram(gl, [sglNodeText("PHONG_VERTEX_SHADER")], [sglNodeText("PHONG_FRAGMENT_SHADER")]);
		////////log(this.prog.log);
		/*************************************************************/ 
		/*************************************************************/
		this.renderer = new SglMeshGLRenderer(this.prog);
		/*************************************************************/ 
		this.disparity     = 0.025;
		this.stereoEnabled = false;
		this.hide_materials=[];
		this.hide_nodes=[]
		this.gldoc = null;	
		var daeSource = { url:document.url_form.url.value, specular:[0.0, 0.0, 0.0, 0.0], light:true, eye:[0.0, -2.0, 1.0] };		
		this.loadDataset(daeSource);		
	},
 
	keyDown : function(gl, keyCode, keyString) {
	
		if (keyString == "R") {
			this.trackball.reset();
		}
 
		if (keyString == "A") {
			this.disparity += 0.005;;
		}
		if (keyString == "Z") {
			this.disparity -= 0.005;;
		}
		
		if (keyCode == "38") {//UPARROW					
		}
		if (keyCode == "39") {//RIGHT
			
		}
		if (keyCode == "40") {//DOWN
			
		}
		if (keyCode == "37") { //LEFT
			
		}		
	},

	mouseMove : function(gl, x, y) {
		var ui = this.ui;
 
		var ax1 = (x / (ui.width  - 1)) * 2.0 - 1.0;
		var ay1 = (y / (ui.height - 1)) * 2.0 - 1.0;
 
		var action = SGL_TRACKBALL_NO_ACTION;
		if ((ui.mouseButtonsDown[0] && ui.keysDown[17]) || ui.mouseButtonsDown[1]) {
			action = SGL_TRACKBALL_PAN;
		}
		else if (ui.mouseButtonsDown[0]) {
			action = SGL_TRACKBALL_ROTATE;
		}
 
		this.trackball.action = action;
		this.trackball.track(this.viewMatrix, ax1, ay1, 0.0);
		this.trackball.action = SGL_TRACKBALL_NO_ACTION;
	},
 
	mouseWheel: function(gl, wheelDelta, x, y) {
		var action = (this.ui.keysDown[16]) ? (SGL_TRACKBALL_DOLLY) : (SGL_TRACKBALL_SCALE);
		var factor = (action == SGL_TRACKBALL_DOLLY) ? (wheelDelta * 0.3) : ((wheelDelta < 0.0) ? (1.10) : (0.90));
 
		this.trackball.action = action;
		this.trackball.track(this.viewMatrix, 0.0, 0.0, factor);
		this.trackball.action = SGL_TRACKBALL_NO_ACTION;
	},
 
	update : function(gl, dt) {
		;
	},
 
	getTexture : function(doc, texName, defaultTex) {
		var tex = doc.textures[texName];
		if (!tex || !tex.isValid) {
			tex = defaultTex;
		}
		return tex;
	},
 
	drawMesh : function(gl, xform, material, mesh, primitive, doc) {	
		var uniforms = {
			u_model_view_projection_mat  : xform.modelViewProjectionMatrix,
			u_model_view_mat             : xform.modelViewMatrix,
			u_view_normal_mat            : xform.viewSpaceNormalMatrix,
			u_view_light_dir             : [ 0.0, 0.0, -1.0, ((doc.sceneInfo.light) ? (0.0) : (1.0)) ],
			u_emission                   : material.emissionCol,
			u_ambient                    : material.ambientCol,
			u_diffuse                    : material.diffuseCol,
			u_specular                   : material.specularCol,
			u_shininess                  : material.shininessVal
		};
 
		var samplers = {
			s_emission                   : this.getTexture(doc, material.emissionMap,  doc.blackTex),
			s_ambient                    : this.getTexture(doc, material.ambientMap,   doc.whiteTex),
			s_diffuse                    : this.getTexture(doc, material.diffuseMap,   doc.whiteTex),
			s_specular                   : this.getTexture(doc, material.specularMap,  doc.whiteTex),
			s_shininess                  : this.getTexture(doc, material.shininessMap, doc.blackTex)
		};
 
		//sglRenderMeshGLPrimitives(mesh, "triangles", this.prog, null, uniforms, samplers);
 
		this.renderer.setUniforms(uniforms);
		this.renderer.setSamplers(samplers);
 
		this.renderer.beginMesh(mesh);
		for (var p in mesh.connectivity.primitives) {
			if (p == primitive) {
				this.renderer.beginPrimitives(p);
					this.renderer.render();
				this.renderer.endPrimitives();
			}
		}
		this.renderer.endMesh();
	},
 
	drawMeshArray : function(gl, xform, material, meshArray, primitive, doc) {
		for (var m in meshArray) {
			var mesh = meshArray[m];
			this.drawMesh(gl, xform, material, mesh, primitive, doc);
		}
	},
 
	drawNode : function(gl, xform, node, doc) {
		xform.model.push();
		xform.model.multiply(node.matrix);
		for (var c in node.children) {
			var child = node.children[c];
			this.drawNode(gl, xform, child, doc);
		}
		for (var m in node.meshGroupIDs) {
			var meshGroup = node.meshGroupIDs[m];
			if (this.hide_materials[meshGroup.materialID]== false) continue; //ALI
			
			var material  = doc.materials[meshGroup.materialID];			
			var meshArray = doc.meshGroups[meshGroup.geometryID];
			
			var primitive = meshGroup.primitiveID;		
			this.drawMeshArray(gl, xform, material, meshArray, primitive, doc);
		}
		xform.model.pop();
	},
 
	drawVisualScene : function(gl, xform, visualScene, doc) {
		xform.model.push();
		for (var n in visualScene.nodes) {
			var node = visualScene.nodes[n];
			this.drawNode(gl, xform, node, doc);
		}
		xform.model.pop();
	},
 
	drawScene : function(gl, xform, scene, doc) {
		xform.model.push();
		for (var v in scene.instanceVisualSceneIDs) {
			var visualScene = doc.instanceVisualScenes[scene.instanceVisualSceneIDs[v]];
			this.drawVisualScene(gl, xform, visualScene, doc);
		}
		xform.model.pop();
	},
 
	drawDocument : function(gl, xform, doc) {
		this.renderer.begin();
 
		xform.model.push();
		for (var s in doc.scenes) {
			var scene = doc.scenes[s];
			this.drawScene(gl, xform, scene, doc);
		}
		xform.model.pop();
 
		this.renderer.end();
	},
 
	draw : function(gl) {
		var w = this.ui.width;
		var h = this.ui.height;
		<?php		
		echo "gl.clearColor(" . $bgColor .  ", 1.0);";
		?>
		gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT | gl.STENCIL_BUFFER_BIT);
 
		gl.viewport(0, 0, w, h);
 
		if (!this.gldoc) return;
 
		this.xform.projection.loadIdentity();
		this.xform.projection.perspective(sglDegToRad(60.0), w/h, 0.1, 100.0);
 
		var bc = this.gldoc.bbox.center;
		//var s  = 7.0 / this.gldoc.bbox.diagonal;
		var s  = 2.0 / this.gldoc.bbox.diagonal;
		var eye = this.gldoc.sceneInfo.eye;
 
		gl.enable(gl.DEPTH_TEST);
		gl.disable(gl.CULL_FACE);
 
		this.xform.model.load(this.trackball.matrix);
 
		if (this.stereoEnabled) {
			//var cpos = sglMulSV3(3.0, sglNormalizedV3([1.0, 2.5, 3.0]));
			//this.camera.lookAt(cpos[0], cpos[1], cpos[2], -0.5, 0.0, 0.0, sglDegToRad(0.0));
			this.camera.lookAt(eye[0], eye[1], eye[2], 0.0, 0.0, 0.0, sglDegToRad(0.0));
			this.viewMatrix = this.camera.matrix;
			this.camera.translate(-this.disparity, 0.0, 0.0);
			this.xform.view.load(this.camera.matrix);
 
			gl.colorMask(true, false, false, false);
			this.xform.model.push();
				this.xform.model.scale(s, s, s);
				this.xform.model.translate(-bc[0], -bc[1], -bc[2]);
				this.drawDocument(gl, this.xform, this.gldoc);
			this.xform.model.pop();
 
			gl.clear(gl.DEPTH_BUFFER_BIT | gl.STENCIL_BUFFER_BIT);
 
			//this.camera.lookAt(cpos[0], cpos[1], cpos[2], -0.5, 0.0, 0.0, sglDegToRad(0.0));
			//this.camera.lookAt(0.0, 0.0, 2.0, 0.0, 0.0, 0.0, sglDegToRad(0.0));
			this.camera.lookAt(eye[0], eye[1], eye[2], 0.0, 0.0, 0.0, sglDegToRad(0.0));
			this.camera.translate(this.disparity, 0.0, 0.0);
 
			this.xform.view.load(this.camera.matrix);
 
			gl.colorMask(false, true, true, true);
			this.xform.model.push();
				this.xform.model.scale(s, s, s);
				this.xform.model.translate(-bc[0], -bc[1], -bc[2]);
				this.drawDocument(gl, this.xform, this.gldoc);
			this.xform.model.pop();
 
			gl.colorMask(true, true, true, true);
		}
		else {
			this.xform.view.load(this.viewMatrix);
 
			this.xform.model.push();
				this.xform.model.scale(s, s, s);
				this.xform.model.translate(-bc[0], -bc[1], -bc[2]);
				this.drawDocument(gl, this.xform, this.gldoc);
			this.xform.model.pop();
		}
 
		gl.disable(gl.DEPTH_TEST);
		gl.disable(gl.CULL_FACE);
	}
};
 
var gColladaTest = new ColladaTest();

function toggleStereo(value) {
	var cb = document.getElementById("STEREO_CHECKBOX");
	gColladaTest.stereoEnabled = cb.checked;
}

function toggleMaterial(id){
	var cb = document.getElementById(id);
	//alert(gColladaTest.hide_materials[id]);
	gColladaTest.hide_materials[id] = cb.checked;	
	//alert(gColladaTest.hide_materials[id]);
}

function changeScene(url_address) {
	var scenes = [	
		{ 		
		url:url_address,specular:[0.0, 0.0, 0.0, 0.0], light:true,   eye:[0.0, 0.0, 100.0] //Top View
		
		}
	]; 	
	var daeSource = scenes[1]; 
	gColladaTest.loadDataset(daeSource);
}

function changeViewSize() {
	var view_size=document.getElementById("view_size");
	var cb = document.getElementById("SGL_CANVAS");
	var ddx=50;
	var ddy=200;	
	switch(view_size.selectedIndex)
	{
	case 1:
	  cb.width =800 ;
	  cb.height =600 ;
	  break;
	case 2:
	  cb.width =1024 ;
	  cb.height =768 ;
	  break;
	case 3:
	  cb.width =1152 ;
	  cb.height =864 ;
	  break;
	 case 4:
	  cb.width =1280 ;
	  cb.height =720 ;
	  break;
	 case 5:
	  cb.width =1280 ;
	  cb.height =800 ;
	  break;
	 case 6:
	  cb.width =1280 ;
	  cb.height =960 ;
	  break;
	case 7:
	  cb.width =1280 ;
	  cb.height =1024 ;
	  break;
	case 8:
	  cb.width =1360 ;
	  cb.height =768 ;
	  break;
	case 9:
	  cb.width =1366 ;
	  cb.height =768 ;
	  break;
	case 10:
	  cb.width =1440 ;
	  cb.height =900 ;
	  break;
	case 11:
	  cb.width =1600 ;
	  cb.height =900 ;
	  break;
	case 12:
	  cb.width =1600 ;
	  cb.height =1200 ;
	  break;
	case 13:
	  cb.width =1680 ;
	  cb.height =1050 ;
	  break;
	case 14:
	  cb.width =1920 ;
	  cb.height =1200 ;
	  break;	
	case 15:
	  cb.width =1920 ;
	  cb.height =1080 ;
	  break;		  
	default:
		cb.width =800 ;
		cb.height =600 ;	  
	}	
	cb.width -= ddx ;
	cb.height -= ddy ;
}


function LoadScene() {		
	var scenes = [{ url:"http://bci52.cib.bau.tu-dresden.de/dae/AC10-Institute-Var-1.dae" ,specular:[0.0, 0.0, 0.0, 0.0], light:true,  eye:[0.0, -2.0, 2.0]  }];	
	var daeSource = scenes[0]; 
	gColladaTest.loadDataset(daeSource);
}
sglRegisterCanvas("SGL_CANVAS", gColladaTest, 10.0);
</script>
<form > 
View size
<select id="view_size" onchange="changeViewSize();">
<option value="7">(5:4)1280x1024pixel</option> 
<option value="1">(4:3)800x600pixel</option> 
<option value="2">(4:3)1024x768pixel</option> 
<option value="3">(4:3)1152x864pixel</option> 
<option value="4">(16:9)1280x720pixel</option> 
<option value="5">(16:10)1280x800pixel</option> 
<option value="6">(4:3)1280x960pixel</option> 
<option value="8">(16:9)1360x768pixel</option> 
<option value="9">(16:9)1366x768pixel</option> 
<option value="10">(16:10)1440x900pixel</option> 
<option value="11">(16:9)1600x900pixel</option> 
<option value="12">(4:3)1600x1200pixel</option>
<option value="13">(16:10)1680x1050pixel</option> 
<option value="14">(16:10)1920x1200pixel</option> 
<option value="15">(16:9)1920x1080pixel</option> 
<option value="16">(16:9)2560x1440pixel</option> 
</select>
URL<input name="url" size="50" value="<?php echo $url?>">
bgcolor(RGB)<input name="bgcolor" size="11" value="<?php echo $bgColor ?>">
Hide<input id="filter_hide" size="20" name="filter_hide" value="<?php echo $filter_hide ?>">
Select<input id="filter_select" size ="20" name="filter_select" value="<?php echo $filter_select ?>">
<input type="submit" value="View">
</form>
<table border="3">
<tr>
<td width="95px" VALIGN="TOP">
List of objects
<select id="Nodes" name="Nodes" size="30" multiple="" >
</select>
</td>
<td>
<canvas id="SGL_CANVAS" width="974" height="568"></canvas> <br />
</td>
</tr>
</table>	
<div id="LayersByMat"></div>
 </body> 
</html> 