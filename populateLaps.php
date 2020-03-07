<html>
<head>
<script type="text/javascript" src="mk/maps.php"></script>
<script type="text/javascript">
async function post(url, body) {
    return await (await fetch(url, {method: 'post', body: body, headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'}})).json();
}
function sleep(t) {
   return new Promise(function(resolve) { 
       setTimeout(resolve, t)
   });
}
function getNextCp(kart) {
	if (oMap.sections) {
		if (kart.tours <= oMap.sections.length)
			return oMap.sections[kart.tours-1];
		return oMap.checkpoint.length-1;
	}
	return 0;
}
function checkpoint(kart) {
	var demitour = kart.demitours;
    var iCP = getNextCp(kart);
    var jCP = (iCP?iCP:oMap.checkpoint.length)-1;
	for (var i=0;i<oMap.checkpoint.length;i++) {
		var oBox = oMap.checkpoint[i];
		var oRect = [oBox[0],oBox[1],15,15];
		oRect[3-oBox[3]] = oBox[2];
		var inRect = pointInRectangle(kart.x,kart.y, oRect);
		if (inRect) {
            if (i==iCP && demitour==jCP)
                return true;
            else if (demitour == i-1 || demitour == i+1) {
                kart.demitours = i;
                return false;
            }
            else if (i==0 && demitour == oMap.checkpoint.length-1) {
                kart.demitours = i;
                return false;
            }
		}
	}
	return false;
}
function pointInRectangle(x,y, oBox) {
	return (x > oBox[0] && x <= oBox[0]+oBox[2] && y > oBox[1] && y <= oBox[1]+oBox[3]);
}
function posKart(oKart, oPlace) {
	var startRotation = oMap.startrotation;
	if (undefined === startRotation)
		startRotation = 180;
	startRotation = startRotation*Math.PI/180;
    var cosStart = Math.cos(startRotation), sinStart = Math.sin(startRotation);
    var nbKartsPerLine = 2;
    var wKarts = 27, hKarts = 130;
    var nbKarts = 1;
    var dir0 = oMap.startdirection||0;
    var shiftX = (((oPlace+1)%nbKartsPerLine)-(nbKartsPerLine-1)/2)*wKarts/(nbKartsPerLine-0.5), shiftY = oPlace*Math.min(12,hKarts/nbKarts);
    if (!dir0)
        shiftX = -shiftX;
    shiftX += 9;
    oKart.x -= shiftX*cosStart + shiftY*sinStart;
    oKart.y += shiftX*sinStart - shiftY*cosStart;
}
let oMap;
let SQL = "";
async function main() {
    let oMaps = listMaps();
    for (let i=1;i<=40;i++) {
        oMap = oMaps["map"+i];
        let gTimes = await post("otherghosts.php", "map="+i);
        for (let j=0;j<gTimes.length;j++) {
            if (!gTimes[j][4]) {
                let gData = await post("ghostrace.php", "id="+gTimes[j][0]);
                let oKart = {
                    x: oMap.startposition[0],
                    y: oMap.startposition[1],
                    tours: 1,
                    demitours: 0,
                };
                let lapTimers = [];
                let timer = 0;
                posKart(oKart,1);
                let ok_ = false;
                //var dd = oKart.tours+" "+oKart.demitours;
                for (let k=0;k<gData.length;k++) {
                    timer++;
                    let aPosX = oKart.x, aPosY = oKart.y;
                    oKart.x = gData[k][0];
                    oKart.y = gData[k][1];
                    var ndd = oKart.tours+" "+oKart.demitours;
                    /*if (ndd != dd) {
                        dd = ndd;
                        console.log(timer*67+"  "+dd);
                    }*/
                    if (checkpoint(oKart)) {
                        let fMoveX = gData[k][0]-aPosX;
                        let fMoveY = gData[k][1]-aPosY;
                        var lastCp = oMap.checkpoint[0];
                        if (oMap.sections)
                            lastCp = oMap.checkpoint[oMap.checkpoint.length-1];
                        var distToCp, dSpeed;
                        if (lastCp[3]) {
                            var distToCp = (aPosY<lastCp[1]) ? (lastCp[1]-aPosY) : (aPosY-lastCp[1]-15);
                            dSpeed = fMoveY;
                        }
                        else {
                            var distToCp = (aPosX<lastCp[0]) ? (lastCp[0]-aPosX) : (aPosX-lastCp[0]-15);
                            dSpeed = fMoveX;
                        }
                        var dt = distToCp/Math.abs(dSpeed);
                        dt = Math.max(0,Math.min(dt,1));
                        if (isNaN(dt-dt)) dt = 0.5;

                        var lapTimer = Math.round((timer+dt-1)*67);

                        var lapTimerSum = 0;
                        for (let l=0;l<lapTimers.length;l++)
                            lapTimerSum += lapTimers[l];

                        oKart.tours++;
                        oKart.demitours = 0;
                        if (oKart.tours == 4) {
                            ok_ = (k >= (gData.length-29));
                            alert(ok_);
                            break;
                        }
                        else
                           lapTimers.push(lapTimer-lapTimerSum);
                    }
                }
                if (!ok_ && oKart.tours == 3 && oKart.demitours == oMap.checkpoint.length-1)
                    ok_ = true;
                else if (lapTimers.length == 1) {
                    lapTimers = [lapTimers[0],Math.round((gTimes[j][3]-lapTimers[0])/2)];
                    ok_ = true;
                }
                var lapTimerSum = 0;
                for (let l=0;l<lapTimers.length;l++)
                    lapTimerSum += lapTimers[l];
                lapTimers.push(gTimes[j][3]-lapTimerSum);
                if (ok_) {
                    SQL += 'UPDATE mkghosts SET lap_times="'+JSON.stringify(lapTimers)+'" WHERE id='+gTimes[j][0]+' AND time="'+gTimes[j][3]+'" AND lap_times="";';
                }
                console.log(i+","+j);
                await sleep(100);
            }
        }
        await sleep(200);
    }
}
main().then(function() {
    document.write(SQL);
});
</script>
</head>
<body>
</body>
</html>