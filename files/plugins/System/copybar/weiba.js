var addCopyStr = function(str) {
	if(window.clipboardData) {
		var text = window.clipboardData.getData("Text");
		window.clipboardData.setData('Text',text + str);
	}else if(window.getSelection) {
      	console.log("b");
		event.preventDefault();
		var selection = window.getSelection();
		event.clipboardData.setData('text/plain', selection + str);
	}else{
		var selection = window.getSelection();
		var body = document.getElementsByTagName('body')[0];
		var range = selection.getRangeAt(0);
		extContent.style.position = 'absolute';
		extContent.style.left = '-99999px';
		extContent.innerHTML = selection + str;
		body.appendChild(extContent);
		selection.selectAllChildren(extContent);
		window.setTimeout(function() {
				body.removeChild(extContent);
				selection.removeAllRanges();
				selection.addRange(range);
				body = selection = null;
		},0);
	}
};

  $("body").on("copy",function(){
    console.log("copy");
    addCopyStr("\r\n本篇文章来源于 ："+location.href+"转载请注明出处");
  });