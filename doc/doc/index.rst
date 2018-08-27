IFC WebServer is a data model server and online viewer for Building Information Models (BIM) based on IFC standards.

It aims to simplify sharing and exchanging of information from BIM models using open and standard formats like (IFC, HTML, XML, CSV, JSON) and check the quality of BIM models (Level of Details, Level of Development). BIM managers and designers can query, filter and make reports about any information inside IFC models easily.

The IFCWebServer has an open structure and can be extended through adding new extensions ( class methods or modules written in Ruby) to add extra functionality. The extensions can be as simple as one line to calculate the area of a circle profile, or very sophisticated like a plug-in to export the data inside the IFC models into other formats like COBie spreadsheets or to extract 2D plans automatically from 3D models.

The project consists mainly of two parts:

Data model server IFCWebServer
IFCWebServer enable 100% access to all information and relations inside IFC models. It supports all IFC official release starting with IFC2X_Final issued on 2001 to the latest release IFC4 Add 2 release issued on July 2016. Moreover, it supports any valid IFC sub schema or extended schema so it can be used by IFC researchers and developer .

The IFCWebServer can be used also in universities as an easy to use tool for BIM courses. Students can register and upload IFC models, explore the structure, apply filters, create sub-models, generate reports, study the IFC data model and compare IFC official releases.

Online BIMViewer
Despite of having easy access to the information in BIM models as the main interest (through queries, filters, scripts and reports), the online 3D visualization offered by BIMViewer provide an handy way to view, share BIM models and visualize the results of data queries online inside the web browser.
