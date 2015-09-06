# Examples of filter expressions #

Filters are string expressions provided by the user to select a set of objects which satisfy a set of conditions, it offers a handy way to generate reports, check for missed attributes information on objects, export a partial model, and more.


here are a list of simple filters which you can try them on the IFC model: **AC10-Institute-Var-1.ifc**

  1. Select the file AC10-Institute-Var-1.ifc from the files list.
  1. Select the IfcSlab class from the classes select lists(or any of its super classes e.g. IfcBuildingElements).
  1. Write the filter expression inside the "Advanced Filter" input box.
  1. Press "go" button or hit the return key.

---

    * get all slabs
```
o.class == IFCSLAB
```

The letter **"o"** refers inside the filter expression to a dummy object and should be written followed be a point before the attributes names

  * get all slab objects which are defined as Roofs
```
o.class == IFCSLAB and o.predefinedType  == '.ROOF.'
```
Here we used a class variable to extend the filter
  * get all slab objects which their names contains the word 'Dach' (it means roof in German)
```
o.class == IFCSLAB and o.name.include?("Dach")
```
Here we used a Ruby method inside the filter !
  * get a certain slab through its globalId attribute
```
o.globalId == "'2WnDGvXIP14xQJyJ3RQPXx'"
```

Note: Attributes names are **case sensitiv**

what about if we like to get a list of all IfcBuildingElement objects which have a non empty "description" attribute ?
of course we can do the same like above and write such a boring filter expression :
```
(o.class == IFCBUILDINGELEMENTCOMPONENT  or
 o.class == IFCBUILDINGELEMENTPART or
 o.class == IFCBUILDINGELEMENTPROXY or
 o.class == IFCCOLUMN or
 o.class == IFCCOVERING or
 o.class == IFCCURTAINWALL or
 o.class == IFCDOOR or
 o.class == IFCFOOTING or
 o.class == .. or
 o.class == .. or
 o.class == .. or
 o.class == IFCWINDOW ) and o.description != '$' and o.description != ''
```
But with some magic we can simply write the filter expression like this:
```
o.class.superclass == IFCBUILDINGELEMENT and o.description.size > 2
```

> so again in short words:filter expressions can be used to select objects depending on their classes,super classes, and their  attributes values.
inside the filter expression you can use any valid Ruby method to form a valid logical expression.
```
and  (or &&)
or ( or | )
not
nil
true
false
==
!=
>
<

"(" and ")" to put conditions in groups

All String class methods:
to_i :convert a string to integer
to_f :conver string to float
start_with?("")
include?("")
size
```

### Note ###

---

  * Attributes names are **case sensitive** and they should start with a small letter .

  * IFC classes should be written in capital letters without ""

  * Filter expressions will be applied only on the selected classes, it means that you have to select at first which classes you are looking inside for the target objects. of course you can click on "All Classes" on the left select list to select all IFC classes.

---


for most common queries you do not need to know a lot about the IFC schema specification, but the more you know, the less effort you need to write advanced filters.


more examples will be added later