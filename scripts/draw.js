
var editorTools = {
	"start": {
		"init" : function(self) {
			self.data = {orientation:180};
		},
		"resume" : function(self) {
			var $startPositions = document.createElementNS(SVG, "svg");
			$startPositions.setAttribute("x",-100);
			$startPositions.setAttribute("y",-100);
			self.state.startPositions = $startPositions;
			$editor.appendChild($startPositions);
			replaceStartPositions(self);
			document.getElementById("start-dir-selector").setValue(self.data.orientation+(self.data.mirror?"r":""));
			var data = deepCopy(self.data);
			delete self.data.pos;
			if (data.pos)
				self.click(self,data.pos,{});
		},
		"click" : function(self,point,extra) {
			if (!self.state.placed) {
				storeHistoryData(self.data);
				setPointPos(self.state.startPositions, {x:point.x-startCenterRotated.x,y:point.y-startCenterRotated.y});
				self.data.pos = point;
				self.state.startPositions.oncontextmenu = function(e) {
					showContextMenu(e,[{
						text: (language ? "Move":"Déplacer"),
						click: function() {
							storeHistoryData(self.data);
							delete self.data.pos;
							self.state.placed = false;
						}
					}]);
					return false;
				};
				self.state.placed = true;
			}
		},
		"move" : function(self,point,extra) {
			if (!self.state.placed)
				setPointPos(self.state.startPositions, {x:point.x-startCenterRotated.x,y:point.y-startCenterRotated.y});
		},
		"save" : function(self,payload) {
			payload.main.startposition = nullablePointToData(self.data.pos);
			if (self.data.pos) {
				var startShiftRotated = startShifts[self.data.orientation];
				payload.main.startposition[0] += startShiftRotated.x;
				payload.main.startposition[1] += startShiftRotated.y;
			}
			payload.main.startrotation = self.data.orientation;
			payload.main.startdirection = self.data.mirror ? 1:0;
		},
		"restore" : function(self,payload) {
			self.data.orientation = payload.main.startrotation;
			if (payload.main.startdirection) self.data.mirror = true;
			self.data.pos = dataToNullablePoint(payload.main.startposition);
			if (self.data.pos) {
				var startShiftRotated = startShifts[self.data.orientation];
				self.data.pos.x -= startShiftRotated.x;
				self.data.pos.y -= startShiftRotated.y;
			}
		},
		"rescale" : function(self, scale) {
			rescaleNullablePoint(self.data.pos, scale);
		},
		"rotate" : function(self, orientation) {
			rotateNullablePoint(self.data.pos, imgSize,orientation);
			self.data.orientation = (self.data.orientation + 360-orientation)%360;
		},
		"flip": function(self, axis) {
			flipNullablePoint(self.data.pos, imgSize,axis);
			self.data.mirror = !self.data.mirror;
			if (axis.coord == "y")
				self.data.orientation = (self.data.orientation + 180)%360;
		}
	},
	"aipoints": {
		"init" : function(self) {
			self.data = [{points:[],closed:false}];
			var $traject = document.getElementById("traject");
			for (var i=0;i<self.data.length;i++) {
				var $trajectOption = document.createElement("option");
				$trajectOption.value = i;
				$trajectOption.innerHTML = (language ? "Route":"Trajet")+" "+(i+1);
				$traject.appendChild($trajectOption);
			}
			var $trajectOption = document.createElement("option");
			$trajectOption.value = -1;
			$trajectOption.className = "special-option";
			$trajectOption.innerHTML = (language ? "More...":"Plus...");
			$traject.appendChild($trajectOption);
			$traject.selectedIndex = 0;
		},
		"resume" : function(self) {
			var traject = +document.getElementById("traject").value;
			self.state.point = createCircle({x:-1,y:-1,r:0.5});
			self.state.point.classList.add("noclick");
			self.state.traject = traject;
			var oldData = self.data[traject];
			self.data[traject] = {points:[],closed:false};
			self.state.data = self.data[traject];
			if (oldData) {
				for (var i=0;i<oldData.points.length;i++)
					self.click(self,oldData.points[i],{});
				if (oldData.closed)
					self.state.nodes[0].circle.onclick();
			}
		},
		"click" : function(self,point,extra) {
			var polygon = self.state.polygon;
			var selfData = self.state.data;
			var selfPoints = selfData.points;
			if (polygon) {
				storeHistoryData(self.data);
				selfPoints.push(point);
				appendPolygonBuilder(self,point);
			}
			else if (selfData.closed) {
				var i = self.state.movingNode;
				if (i !== undefined) {
					$toolbox.classList.remove("hiddenbox");
					self.move(self,point,extra);
					self.state.data.points[i] = point;
					$editor.removeChild(self.state.mask);
					delete self.state.movingNode;
				}
			}
			else {
				if (!extra.oob) {
					storeHistoryData(self.data);
					selfPoints.push(point);
					startPolygonBuilder(self,point, {
						min_points: 1,
						hollow: true,
						allow_undos: true,
						custom_undos: true,
						keep_nodes: true,
						keep_box: true,
						on_apply: function(polygon,points) {
							$editor.removeChild(polygon);
							self.state.polygon = null;
							selfData.closed = true;
							createPolyline(self,points);
						}
					});
				}
			}
		},
		"move" : function(self,point,extra) {
			if (self.state.data.closed) {
				var i = self.state.movingNode;
				if (i !== undefined) {
					self.state.nodes[i].move(point);
					var lines = self.state.lines;
					var line1 = lines[(i+lines.length-1)%lines.length], line2 = lines[i];
					line1.setAttribute("x2", point.x);
					line1.setAttribute("y2", point.y);
					line2.setAttribute("x1", point.x);
					line2.setAttribute("y1", point.y);
				}
			}
			else
				movePolygonBuilder(self,point);
		},
		"round_on_pixel" : function(self) {
			return true;
		},
		"save" : function(self,payload) {
			payload.main.aiclosed = [];
			payload.aipoints = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				payload.main.aiclosed.push(iData.closed ? 1:0);
				payload.aipoints.push(polyToData(iData.points));
			}
		},
		"restore" : function(self,payload) {
			currentMode = "aipoints";
			for (var i=1;i<payload.aipoints.length;i++) {
				try {
					addTraject();
				}
				catch (e) {
					// TODO c'est moche
				}
			}
			document.getElementById("traject").selectedIndex = 0;
			for (var i=0;i<payload.aipoints.length;i++)
				self.data[i] = {closed:payload.main.aiclosed[i]==1,points:dataToPoly(payload.aipoints[i])};
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescalePoly(self.data[i].points, scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotatePoly(self.data[i].points, imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipPoly(self.data[i].points, imgSize,axis);
		}
	},
	"walls": commonTools["walls"],
	"offroad": commonTools["offroad"],
	"holes": commonTools["holes"],
	"checkpoints": {
		"init": function(self) {
			self.data = {
				"checkpoints": [],
				"type": 0,
				"nb": 3
			}
		},
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.point.classList.add("noclick");
			self.state.rectangles = [];
			var data = self.data.checkpoints;
			self.data.checkpoints = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.click(self,iData,{});
				self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
			}
			updateLapsCounter();
		},
		"click" : function(self,point,extra) {
			if (self.state.rectangle)
				appendRectangleBuilder(self,point);
			else {
				if (!extra.oob) {
					startRectangleBuilder(self,point, {
						cp: true,
						on_apply: function(rectangle,data) {
							var selfData = self.data.checkpoints;
							selfData.push(data);
							var cpIdText = document.createElementNS(SVG, "text");
							cpIdText.setAttribute("class", "dark noclick");
							cpIdText.setAttribute("font-size", 15);
							cpIdText.setAttribute("text-anchor", "middle");
							cpIdText.setAttribute("dominant-baseline", "middle");
							self.state.rectangles.push(rectangle);
							rectangle.reorder = function() {
								cpIdText.innerHTML = 1+selfData.indexOf(data);
							};
							rectangle.reposition = function(nData) {
								cpIdText.setAttribute("x", nData.x+nData.w/2);
								cpIdText.setAttribute("y", nData.y+nData.h/2+2);
							};
							rectangle.reorder();
							rectangle.reposition(data);
							$editor.appendChild(cpIdText);
							addContextMenuEvent(rectangle, [{
								text: (language ? "Resize":"Redimensionner"),
								click: function() {
									resizeRectangle(rectangle,data,{cp:true});
								}
							}, {
								text: (language ? "Move":"Déplacer"),
								click: function() {
									moveRectangle(rectangle,data,{cp:true});
								}
							}, {
								text: (language ? "Change Order":"Changer ordre"),
								click: function() {
									var order = selfData.indexOf(data);
									var nOrder = prompt(language ? "Set new checkpoint position:":"Spécifier position du checkpoint :", 1+order)-1;
									if (nOrder!=order && nOrder>=0 && nOrder<selfData.length) {
										storeHistoryData(self.data);
										selfData.splice(order,1);
										selfData.splice(nOrder,0, data);
									}
									for (var i=0;i<self.state.rectangles.length;i++)
										self.state.rectangles[i].reorder();
								}
							}, {
								text:(language ? "Delete":"Supprimer"),
								click:function() {
									$editor.removeChild(rectangle);
									$editor.removeChild(cpIdText);
									storeHistoryData(self.data);
									removeFromArray(selfData,data);
									removeFromArray(self.state.rectangles,rectangle);
									for (var i=0;i<self.state.rectangles.length;i++)
										self.state.rectangles[i].reorder();
								}
							}]);
						}
					});
				}
			}
		},
		"move" : function(self,point,extra) {
			moveRectangleBuilder(self,point);
		},
		"save" : function(self,payload) {
			payload.checkpoint = [];
			var selfData = self.data.checkpoints;
			for (var i=0;i<selfData.length;i++) {
				var iData = selfData[i];
				var iPayload = [iData.x,iData.y,0,0];
				if (iData.h != 15) {
					iPayload[2] = iData.h;
					iPayload[3] = 0;
				}
				else {
					iPayload[2] = iData.w;
					iPayload[3] = 1;
				}
				payload.checkpoint.push(iPayload);
			}
			payload.main.tours = self.data.nb;
			if (self.data.type)
				payload.main.sections = self.data.sections;
		},
		"restore" : function(self,payload) {
			var selfData = self.data.checkpoints;
			for (var i=0;i<payload.checkpoint.length;i++) {
				var iPayload = payload.checkpoint[i];
				var iData = {x:iPayload[0],y:iPayload[1]};
				if (iPayload[3]) {
					iData.w = iPayload[2];
					iData.h = 15;
				}
				else {
					iData.w = 15;
					iData.h = iPayload[2];
				}
				selfData.push(iData);
			}
			self.data.nb = payload.main.tours;
			if (payload.main.sections) {
				self.data.type = 1;
				self.data.sections = payload.main.sections;
			}
			else
				self.data.type = 0;
		},
		"rescale" : function(self, scale) {
			var keys = ["x","y"];
			var keys2 = ["w","h"];
			var plus = new Array();
			var iInfo = [];
			for (var j=0;j<self.data.checkpoints.length;j++) {
				var jInfo = rectToData(self.data.checkpoints[j]);
				if (jInfo[3] != 15) {
					jInfo[2] = jInfo[3];
					jInfo[3] = 0;
				}
				else
					jInfo[3] = 1;
				iInfo.push(jInfo);
			}
			var rapports = [scale.x, scale.y];
			var plus = new Array();
			for (var j=0;j<iInfo.length;j++) {
				plus[j] = [0,0];
				plus[j][iInfo[j][3]] = (iInfo[j][iInfo[j][3]] > iInfo[(j?j:iInfo.length)-1][iInfo[j][3]])*15;
			}
			for (var j=0;j<iInfo.length;j++) {
				for (var k=0;k<2;k++)
					iInfo[j][k] = Math.round((iInfo[j][k]+plus[j][k])*rapports[k]-plus[j][k]);
				iInfo[j][2] = Math.round(iInfo[j][2]*rapports[1-iInfo[j][3]]+rapports[1-iInfo[j][3]]-1);
			}
			for (var j=0;j<iInfo.length;j++) {
				var jInfo = iInfo[j];
				if (jInfo[3])
					jInfo[3] = 15;
				else {
					jInfo[3] = jInfo[2];
					jInfo[2] = 15;
				}
				self.data.checkpoints[j] = dataToRect(jInfo);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.checkpoints.length;i++)
				rotateRect(self.data.checkpoints[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.checkpoints.length;i++)
				flipRect(self.data.checkpoints[i], imgSize,axis);
		}
	},
	"items": commonTools["items"],
	"jumps": commonTools["jumps"],
	"boosts": commonTools["boosts"],
	"decor": commonTools["decor"],
	"cannons": commonTools["cannons"],
	"mobiles": commonTools["mobiles"],
	"options": commonTools["options"]
};