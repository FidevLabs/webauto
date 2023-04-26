function VoirImg(Limg) {
    LeHtml="<img border=0  width='400' src='"+Limg+"'style='box-shadow: 3px 3px 2.1em black;border-radius:5px;position: absolute;top: 50%;left: 50%;z-index: 200' >";
    
    if (document.layers) {
      document.layers["MonDiv"].document.write(LeHtml);
      document.layers["MonDiv"].document.close();
      document.layers["MonDiv"].top=20;
      document.layers["MonDiv"].left=112;
      document.layers["MonDiv"].visibility="show";}
    if (document.all) {
      MonDiv.innerHTML=LeHtml;
      document.all["MonDiv"].style.top=20;
      document.all["MonDiv"].style.left=120;
      document.all["MonDiv"].style.visibility="visible";
    }
    else if (document.getElementById) {
      document.getElementById("MonDiv").innerHTML=LeHtml;
      document.getElementById("MonDiv").style.top=20;
      document.getElementById("MonDiv").style.left=120;
      document.getElementById("MonDiv").style.visibility="visible";
    }
  }

  
  function CacheMonDiv() {
          if (document.layers) {document.layers["MonDiv"].visibility="hide";}
          if (document.all) {document.all["MonDiv"].style.visibility="hidden";}
          else if (document.getElementById){document.getElementById("MonDiv").style.visibility="hidden";}
  }
  
  function CreDiv() {
      if (document.layers) {
          document.write("<LAYER name='MonDiv' top=10 left=100 visibility='hide'></LAYER>");
      }
      if (document.all) {
          document.write("<div id='MonDiv' style='position:absolute;top:41%;left:58%;visibility:hidden'></div>");
      }
      else if (document.getElementById) {
          document.write("<div id='MonDiv' style='position:absolute;top:41%;left:58%;visibility:hidden'></div>");
      }
  }

$(document).ready(function () {
  $('.fileupload-table a').on('mouseover', function (e) {
      e.preventDefault();
      VoirImg($(this).attr('href'));
  });

  $('.fileupload-table a').on('mouseout', function (e) {
      e.preventDefault();
      CacheMonDiv();
  });
});