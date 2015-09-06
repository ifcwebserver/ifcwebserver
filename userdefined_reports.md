# User-defined Reports #

The user can select which fields (attribute) to be included in the query result by providing a user-defined report expression. By default all direct attributes of the IFC object instances will be included.

The syntax of user-defined reports expression is very simple:
```
[ColumnHeader1:]attributeName1|[ColumnHeader2:]attributeName2|..|..|[ColumnHeaderN:]attributeNameN
```
Example:
```
line_id|globalId|name|representation|tag|objectType|predefinedType|description
```
You can change the default name of column headers like this:
```
ID:line_id|GUID:globalId|name|representation|tag|objectType|predefinedType|description
```