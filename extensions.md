## What are extensions ? ##
Extensions are classes methods. They extend the functionality of IFC classes and the [IfcWebServer](http://code.google.com/p/ifcwebserver) core functions by adding a new functions on-the-fly on the level of the  server or on the level of each user.

**Server Extension repository:** https://github.com/ifcwebserver/IFCWebServer-Extensions


For example all sub classes of [IfcParameterizedProfileDef](http://buildingsmart-tech.org/ifc/IFC2x3/TC1/html/ifcprofileresource/lexical/ifcparameterizedprofiledef.htm) contain  the basic information to describe the shape of the profile(like I T U L C Z Circle,Rectangle) but nothing more nothing less.

Wouldn't be nice if we have a method called "area" for all of these profile classes? so we can make advanced quires like "give me all building elements which are defined through a parameterized profile and have a section area bigger than xxx m<sup>2</sup>", or to create reports include the profile section area.
```
http://www.ifcwebserver.org/ifc.rb?
ifc_file=AC10-Institute-Var-1.ifc
&filter=o.area>10
&report_cols=area
&output_format=to_html
&q=IFCARBITRARYCLOSEDPROFILEDEF 
```
Get all ifcArbitraryClosedProfileDef instances which have a section area bigger than 10 m<sup>2</sup> in the test file 'AC10-Institute-Var-1.ifc'

Result : 97 out of 483

In short words "Extensions" are the added value of the basic information and a handy way to extended the functionality of [IfcWebServer](http://code.google.com/p/ifcwebserver) easily.


## How to extend/modfiy IfcWebServer ##

As a final user may you wish to add new features or to modify something to meet your needs. You don not have to be a professional programmer to be able to write your own extensions. The hard job is already done for you.

### Example 1 ###
As a first example, let us assume that you need to use the area value of windows and doors inside your queries/reports.According to the IFC specification the area is not saved inside the IFC files, but it can be calculated simply through the attributes "overallHeight" and "overallWidth", so we can write a simple extension methods "area" like this :
```
class IFCWINDOW 
  def area
   @overallHeight.to_f*@overallWidth.to_f
  end
end

class IFCDOOR 
  def area
   @overallHeight.to_f*@overallWidth.to_f
  end
end
```
Now the method "area" is available for all object instances of IfcWindow and IfcDoor and can be used inside the filter expressions or to be included inside reports.

Try it :

  * Get a list contains the area value of all doors and windows in the IFC model "AC10-Institute-Var-1.ifc":
```
http://www.ifcwebserver.org/ifc.rb?
ifc_file=AC10-Institute-Var-1.ifc
&report_cols=area
&q=IFCDOOR|IFCWINDOW
```


  * Create a report for all doors which have area > 3 m<sup>2</sup>
```
http://www.ifcwebserver.org/ifc.rb?
ifc_file=AC10-Institute-Var-1.ifc
&filter=o.area>3
&report_cols=globalId|line_id|name|area
&q=IFCDOOR
```

**Result:** 10 out of 77 doors

### Example 2 ###
The second example allow us to list the colors which are used inside the IFC model. IFC spesification for the class [IfcColourRgb](http://buildingsmart-tech.org/ifc/IFC2x3/TC1/html/ifcpresentationresource/lexical/ifccolourrgb.htm) defines colours as 3 numbers for Red,Green and Blue :

```
ENTITY IfcColourRgb
SUBTYPE OF (	IfcColourSpecification);
Red	 : 	IfcNormalisedRatioMeasure;
Green	 : 	IfcNormalisedRatioMeasure;
Blue	 : 	IfcNormalisedRatioMeasure;
END_ENTITY;
```

The extension code may look like this :
```
class IFCCOLOURRGB
  def to_RGB_HEX
   "#" + (sprintf("%02x",@red.to_f*255) + sprintf("%02x", @green.to_f*255) + sprintf("%02x",@blue.to_f*255)).upcase	
  end

  def to_html_div
   "<div style=\"background-color:" + to_RGB_HEX + "\">&nbsp;</div>"
  end
end
```

now the two new methods **to\_html\_div** and **to\_RGB\_HEX** are available and ready to use for all IfcColourRgb object instances.

Try it:
```
http://www.ifcwebserver.org/ifc.rb?
ifc_file=FZK-Haus-AC11-IFC.ifc
&report_cols=to_html_div|to_RGB_HEX
&q=IFCCOLOURRGB
```

**Result:**

![http://ifcwebserver.googlecode.com/svn/doc/ifcwebServer_RGB_colors.png](http://ifcwebserver.googlecode.com/svn/doc/ifcwebServer_RGB_colors.png)

### Advanced example ###
To get a list of objects (class,globalId,name) and their associated properties (or subset of objects and a subset of their properties ).


```
http://www.ifcwebserver.org/ifc.rb?ifc_file=xxxx.ifc&q=IFCRELDEFINESBYPROPERTIES


Report_cols:
class:relatedObjects.to_obj.class|globalId:relatedObjects.to_obj.globalId|name:relatedObjects.to_obj.name
|Attributes:relatingPropertyDefinition.to_obj.property_details

Advanced Filter:
o.relatedObjects.to_s.to_obj.class.superclass == IFCBUILDINGELEMENT
```

![http://www.ifcwebserver.org/doc/extensions_advanced_example.png](http://www.ifcwebserver.org/doc/extensions_advanced_example.png)

![http://www.ifcwebserver.org/doc/extensions_advanced_example_res.png](http://www.ifcwebserver.org/doc/extensions_advanced_example_res.png)

Note: _property\_details_ is an extension function of the class IfcPropertySet. It prints the property list as HTML table.