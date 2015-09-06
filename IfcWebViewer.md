## Introduction ##

IFCWebViewer is a part of IFCWebServer project. It enables viewing 3D BIM models online inside a modern web browser without installing any plugin.

http://www.bimviewer.com

http://www.ifcwebserver.org/sgl

(You need a [WebGL enabled browser](http://get.webgl.org/) to be able to use IFC WebViewer (Google Chrome/Firefox), MS Internet Explorer is NOT supported)

[How to Enable WebGL in FireFox, Chrome and Safari](http://www.windows7hacker.com/index.php/2010/02/how-to-enable-webgl-in-firefox-chrome-and-safari/)

## Main features ##
  * Simple layer management (hide, show, change the default color)
  * Walkthrough navigation (mouse & keyboard keys)
  * Creating simple sections(parallel to the camera)
  * Hiding/selecting 3D objects by name/globalId

## How it works? ##
IFCWebServer converts the geometry representation of IFC objects into the  open and standard 3D format "COLLADA". The render takes place in the browser with help of the [SpiderGL](http://spidergl.org/) Java script library.

At moment the fellowing classess are supported:
IfcBoundingBox
IfcClosedShell
IfcConnectedFaceSet
IfcExtrudedAreaSolid
IfcFace
IfcFaceBound
IfcFaceOuterBound
IfcFacetedBrep
IfcGridAxis
IfcMappedItem
IfcOpenShell
IfcPolyline


## Upload your own 3D models ##
If you like to view/share your IFC/3D models online using IFCWebViewer  upload your BIM models to the demo server (IFC format) or convert them to one of the following 3D formats:
  * AutoDesk 3ds: you can use [DDS-Viewer](ftp://ftp.dds.no/pub/install/IfcViewer/DDSViewer.exe)
  * Wavefront .obj: you can use [IfcOpenShell](http://ifcopenshell.org/)
  * blender [IfcOpenShell-plugin](http://ifcopenshell.org/)
  * dae (COLLADA format- exported from Blender)

## Quick help ##
![http://i.imm.io/kNWf.png](http://i.imm.io/kNWf.png)

Rotate: Left click + move

Zoom: mouse wheel

Pan: Middle button + move

Walkthrough keys:
W,A, D, S,Q , E (+ Space key: 200% faster)
R reset the camera location

## How to create a simple section ##
![http://i.imm.io/kNVL.png](http://i.imm.io/kNVL.png)

---

## Screenshots ##

![http://i.imm.io/ggjB.png](http://i.imm.io/ggjB.png)

![http://i.imm.io/gfbs.png](http://i.imm.io/gfbs.png)

![http://i.imm.io/ggex.jpeg](http://i.imm.io/ggex.jpeg)