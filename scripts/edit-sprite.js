window.onload = function() {
	var canvas = document.getElementById("select-transparency");
	if (canvas) {
		var ctx = canvas.getContext("2d");
		ctx.imageSmoothingEnabled = false;
		var img = document.createElement("img");
		img.src = spriteSrc;
		img.onload = function() {
			ctx.drawImage(img, 0,0, img.naturalWidth,img.naturalHeight, 0,0, spriteW,spriteH);
			canvas.onclick = function(e) {
				var x = e.pageX - this.offsetLeft; 
				var y = e.pageY - this.offsetTop;
    			var color = ctx.getImageData(x, y, 1, 1).data;
    			color = color[0]+","+color[1]+","+color[2];
    			var form = document.getElementById("transparency-form");
    			form.color.value = color;
    			document.getElementById("transparency-color-preview").style.backgroundColor = "rgb("+color+")";
    			form.style.display = "block";
			};
		};
	}
}