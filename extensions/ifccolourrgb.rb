class IFCCOLOURRGB
  def to_RGB_HEX
   "#" + (sprintf("%02x",@red.to_f*255) + sprintf("%02x", @green.to_f*255) + sprintf("%02x",@blue.to_f*255)).upcase     
  end
  def to_html_div
   "<div style=\"background-color:" + to_RGB_HEX + "\">&nbsp;</div>"
  end
 end