#IfcWebServer can convert the EXPRESS entities automatically to classes but the rules must be validated through special methods, in general case we can ignore most of the rules and assume that they are valid. Adding validation methods help to improve the quality of the data inside the IFC model, for example to check if all date are valid, or to avoid having wrong relations between objects (e.g. an object which nested to itsself through IfcRelNests)

#You can help us to improve IfcWebServer by adding more rules.It is a good practice to understand how IfcWebServer works and how you can easily extend it.

class IFCACTORROLE
	def validate_rules
   #Author:Ali.Ismail
		#WR1 : (Role <> IfcRoleEnum.USERDEFINED) OR ((Role = IfcRoleEnum.USERDEFINED) AND  EXISTS(SELF.UserDefinedRole));
		if (@role != ".USERDEFINED." or (@role == ".USERDEFINED." and (@userDefinedRole != '$' and @userDefinedRole != '' ))) == false then
			puts "<div style='background-color:rgb(255, 230, 230);padding: 16px ;width:50%' >"
			puts self.class.to_s + "<br>"
			puts "The Rule:<br>WR1 : (Role <> IfcRoleEnum.USERDEFINED) OR ((Role = IfcRoleEnum.USERDEFINED) AND  EXISTS(SELF.UserDefinedRole));"
			puts "is broken\n"
			puts $hash["#" +@line_id.to_s]	
			puts "</div>"			
		end	
	end
end

class IFCPERSON
  def validate_rules
  #Author:Ali.Ismail
		#	WR1 : EXISTS(FamilyName) OR   EXISTS(GivenName);		
		if (@familyName== "$" or @familyName== "''")  and (@givenName== "$" or @givenName== "''")
			puts "<div style='background-color:rgb(255, 230, 230);padding: 16px ;width:50%' >"
			puts self.class.to_s + "<br>"
			puts "The Rule:<br>WR1 : EXISTS(FamilyName) OR   EXISTS(GivenName);"
			puts "is broken</br>"
			puts $hash["#" +@line_id.to_s]	
			puts "</div>"			
		end	
	end
end

class IFCRELNESTS
	def validate_rules
   #Author:Ali.Ismail
		#NoSelfReference WR1 : SIZEOF(QUERY(Temp <* SELF\IfcRelDecomposes.RelatedObjects | NOT(TYPEOF(SELF\IfcRelDecomposes.RelatingObject) = TYPEOF(Temp)))) = 0;
		if @relatedObjects.to_s.include?(@relatingObject.to_s) and @relatedObjects != nil			
			puts 
			puts "<div style='background-color:rgb(255, 230, 230);padding: 16px ;width:50%' >"
			puts self.class.to_s + "<br>"
			puts "The Rule:<b>NoSelfReference</b><br> WR1 : SIZEOF(QUERY(Temp <* SELF\IfcRelDecomposes.RelatedObjects | NOT(TYPEOF(SELF\IfcRelDecomposes.RelatingObject) = TYPEOF(Temp)))) = 0;"
			puts "</br>is broken</br>"
			puts $hash["#" +@line_id.to_s]	
			puts "</br>The object:<b>" + @relatingObject.to_s + "</b> can not be nested to itself"
			puts "</div>"
		end
	end
end


require 'date'
class IFCCALENDARDATE 
   
  def validate_rules
  #Author:Ali.Ismail
  begin			
			Date.new(@yearComponent, @monthComponent, @dayComponent)
		rescue 
			$log["#" +@line_id.to_s ]=  "<div style='background-color:rgb(255, 230, 230);padding: 16px ;width:50%' >" + self.class.to_s + "<br>The Rule:<br>WR21 : IfcValidCalendarDate (SELF);\nis broken\n" + $hash["#" +@line_id.to_s] +"</div>"
		end	
  end
end

class IFCZONE
	  def validate_rules 
    #Author:Ali.Ismail
		#IFC2x3
		#WR1 : 	SIZEOF (QUERY (temp <* SELF\IfcGroup.IsGroupedBy.RelatedObjects | NOT(('IFCPRODUCTEXTENSION.IFCZONE' IN TYPEOF(temp)) OR ('IFCPRODUCTEXTENSION.IFCSPACE' IN TYPEOF(temp))) )) = 0;
		#IFC2x4
		#WR1 : 	(SIZEOF(SELF\IfcGroup.IsGroupedBy) = 0) OR (SIZEOF (QUERY (temp <* SELF\IfcGroup.IsGroupedBy[1].RelatedObjects | NOT(('IFC2X4_ALPHA.IFCZONE' IN TYPEOF(temp)) OR ('IFC2X4_ALPHA.IFCSPACE' IN TYPEOF(temp)) OR ('IFC2X4_ALPHA.IFCSPATIALZONE' IN TYPEOF(temp)) ))) = 0);
		# A Zone is grouped by the objectified relationship IfcRelAssignsToGroup. Only objects of type IfcSpace or IfcZone are allowed as RelatedObjects.
		
		if @isGroupedBy != nil 
			isGroupedByObj=$ifcObjects[@isGroupedBy].relatedObjects.split("#").each { |relObj_ID|
			if $ifcObjects[relObj_ID].class.to_s == "IFCSPACE" or $ifcObjects[relObj_ID].class.to_s == "IFCZONE" then
			else
				puts "<div style='background-color:rgb(255, 230, 230);padding: 16px ;width:50%' >"
				puts self.class.to_s + "<br>"
				puts "The Rule:<br>WR1 : A Zone is grouped by the objectified relationship IfcRelAssignsToGroup. Only objects of type IfcSpace or IfcZone are allowed as RelatedObjects."
				puts "</br>is broken</br>"
				puts $hash["#" +@line_id.to_s]	
				puts "#" + relObj_ID.to_s + " class =" + $ifcObjects[relObj_ID].class.to_s
				puts "</div>"
			end;
			}					
		end	
	end
end