
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
	"walls": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					break;
				}
			}
			replaceNodeType(self);
			document.getElementById("walls-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								self.data.push(data);
								addContextMenuEvent(rectangle,[{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(rectangle);
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								self.data.push(data);
								polygon.setAttribute("stroke-width", 1);
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(polygon);
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.shape == "polygon";
		},
		"save" : function(self,payload) {
			payload.collision = [];
			for (var i=0;i<self.data.length;i++)
				payload.collision.push(shapeToData(self.data[i]));
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.collision.length;i++)
				self.data.push(dataToShape(payload.collision[i]));
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleShape(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateShape(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipShape(self.data[i], imgSize,axis);
		}
	},
	"offroad": {
		"init" : function(self) {
			self.data = [];
			for (var i=0;i<hpTypes.length;i++)
				self.data.push([]);
		},
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			var offroadType = +document.getElementById("offroad-type").value;
			var oldData = self.data[offroadType];
			self.data[offroadType] = [];
			self.state.type = offroadType;
			self.state.data = self.data[offroadType];
			self.state.shape = "rectangle";
			for (var i=0;i<oldData.length;i++) {
				var iData = oldData[i];
				self.state.shape = iData.type;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					break;
				}
			}
			replaceNodeType(self);
			document.getElementById("offroad-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								self.state.data.push(data);
								addContextMenuEvent(rectangle,[{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(rectangle);
										storeHistoryData(self.data);
										removeFromArray(self.state.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								self.state.data.push(data);
								polygon.setAttribute("stroke-width", 1);
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(polygon);
										storeHistoryData(self.data);
										removeFromArray(self.state.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.shape == "polygon";
		},
		"save" : function(self,payload) {
			payload.horspistes = {};
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				if (iData.length) {
					var iPayload = [];
					for (var j=0;j<iData.length;j++)
						iPayload.push(shapeToData(iData[j]));
					payload.horspistes[hpTypes[i]] = iPayload;
				}
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<hpTypes.length;i++) {
				var iPayload = payload.horspistes[hpTypes[i]];
				var iData = [];
				if (iPayload) {
					for (var j=0;j<iPayload.length;j++)
						iData.push(dataToShape(iPayload[j]));
				}
				self.data[i] = iData;
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					rescaleShape(iData[j], scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					rotateShape(iData[j], imgSize,orientation);
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					flipShape(iData[j], imgSize,axis);
			}
		}
	},
	"holes": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.orientation = 2;
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				if (undefined !== iData.orientation)
					self.state.orientation = iData.orientation;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					break;
				}
				if (iData.respawn)
					self.click(self,iData.respawn,{});
			}
			replaceNodeType(self);
			document.getElementById("holes-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var respawnNode = self.state.respawnNode;
			if (respawnNode) {
				if (extra.oob)
					return;
				storeHistoryData(self.data);
				var data = self.data[self.data.length-1];
				data.respawn = point;
				respawnNode.move(point);
				var lastRotateTime = 0;
				respawnNode.origin.classList.add("hover-toggle");
				respawnNode.origin.onclick = function(e) {
					if (e) e.stopPropagation();
					var nRotateTime = new Date().getTime();
					if (nRotateTime > lastRotateTime+2500)
						storeHistoryData(self.data);
					lastRotateTime = nRotateTime;
					data.orientation = (data.orientation+1)%4;
					self.state.orientation = data.orientation;
					respawnNode.rotate(data.orientation);
				};
				var shape = self.state.currentshape;
				delete self.state.currentshape;
				function deleteItem() {
					$editor.removeChild(shape);
					$editor.removeChild(respawnNode.origin);
					var lines = respawnNode.lines;
					for (var i=0;i<lines.length;i++)
						$editor.removeChild(lines[i]);
					storeHistoryData(self.data);
					removeFromArray(self.data,data);
				}
				respawnNode.origin.oncontextmenu = function(e) {
					return showContextOnElt(e,shape, [{
						text: (language ? "Rotate":"Pivoter"),
						click: function() {
							respawnNode.origin.onclick();
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveArrow(respawnNode,data.respawn);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
				};
				switch (data.type) {
				case "rectangle":
					addContextMenuEvent(shape,[{
						text: (language ? "Resize":"Redimensionner"),
						click: function() {
							resizeRectangle(shape,data);
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveRectangle(shape,data);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				case "polygon":
					addContextMenuEvent(shape, [{
						text: (language ? "Edit":"Modifier"),
						click: function() {
							editPolygon(shape,data);
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							movePolygon(shape,data);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				}
				delete self.state.respawnNode;
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					if (self.state.rectangle)
						appendRectangleBuilder(self,point);
					else {
						if (!extra.oob) {
							startRectangleBuilder(self,point, {
								on_apply: function(rectangle,data) {
									self.state.currentshape = rectangle;
									data.orientation = self.state.orientation;
									self.data.push(data);
									self.state.respawnNode = createArrowNode({x:-10,y:-10},self.state.orientation);
								}
							});
						}
					}
					break;
				case "polygon":
					if (self.state.polygon)
						appendPolygonBuilder(self,point);
					else {
						if (!extra.oob) {
							startPolygonBuilder(self,point, {
								on_apply: function(polygon,points) {
									self.state.currentshape = polygon;
									var data = {type:"polygon",points:points};
									data.orientation = self.state.orientation;
									self.data.push(data);
									polygon.setAttribute("stroke-width", 1);
									self.state.respawnNode = createArrowNode(deepCopy(point),self.state.orientation);
								}
							});
						}
					}
					break;
				}
			}
		},
		"move" : function(self,point,extra) {
			if (self.state.respawnNode)
				self.state.respawnNode.move(point);
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					moveRectangleBuilder(self,point);
					break;
				case "polygon":
					movePolygonBuilder(self,point);
					break;
				}
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.respawnNode || (self.state.shape == "polygon");
		},
		"save" : function(self,payload) {
			payload.trous = [[],[],[],[]];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var shape = shapeToData(iData);
				var respawn = nullablePointToData(iData.respawn);
				payload.trous[iData.orientation||0].push([shape,respawn]);
			}
		},
		"restore" : function(self,payload) {
			for (var j=0;j<4;j++) {
				for (var i=0;i<payload.trous[j].length;i++) {
					var iPayload = payload.trous[j][i];
					var iData = dataToShape(iPayload[0]);
					iData.respawn = dataToNullablePoint(iPayload[1]);
					if (iData.respawn)
						iData.orientation = j;
					self.data.push(iData);
				}
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rescaleShape(iData, scale);
				rescaleNullablePoint(iData.respawn, scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotateShape(iData, imgSize,orientation);
				rotateNullablePoint(iData.respawn, imgSize,orientation);
				if (undefined !== iData.orientation)
					iData.orientation = (iData.orientation + 4-orientation/90)%4;
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipShape(iData, imgSize,axis);
				flipNullablePoint(iData.respawn, imgSize,axis);
				if ((iData.orientation%2) == (axis.coord=="x" ? 1:0))
					iData.orientation = (iData.orientation+2)%4;
			}
		}
	},
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
	"items": {
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.click(self,iData,{});
			}
		},
		"click" : function(self,point,extra) {
			if (extra.oob)
				return;
			self.move(self,point,extra);
			storeHistoryData(self.data);
			self.data.push(point);
			var box = self.state.point;
			box.classList.remove("noclick");
			box.oncontextmenu = function(e) {
				hideBox(self.state.point,self.state.boxSize);
				return showContextOnElt(e,box,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						moveBox(box,point,self.state.boxSize);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						$editor.removeChild(box);
						storeHistoryData(self.data);
						removeFromArray(self.data,point);
					}
				}]);
			};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
		},
		"move" : function(self,point,extra) {
			setBoxPos(self.state.point,point,self.state.boxSize);
		},
		"save" : function(self,payload) {
			payload.arme = [];
			for (var i=0;i<self.data.length;i++)
				payload.arme.push(pointToData(self.data[i]));
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.arme.length;i++)
				self.data.push(dataToPoint(payload.arme[i]));
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleBox(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateBox(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipBox(self.data[i], imgSize,axis);
		}
	},
	"jumps": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.point.classList.add("noclick");
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.click(self,iData,{});
				self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
			}
		},
		"click" : function(self,point,extra) {
			if (self.state.rectangle)
				appendRectangleBuilder(self,point);
			else {
				if (!extra.oob) {
					startRectangleBuilder(self,point, {
						on_apply: function(rectangle,data) {
							self.data.push(data);
							addContextMenuEvent(rectangle,[{
								text: (language ? "Resize":"Redimensionner"),
								click: function() {
									resizeRectangle(rectangle,data);
								}
							}, {
								text: (language ? "Move":"Déplacer"),
								click: function() {
									moveRectangle(rectangle,data);
								}
							}, {
								text:(language ? "Delete":"Supprimer"),
								click:function() {
									$editor.removeChild(rectangle);
									storeHistoryData(self.data);
									removeFromArray(self.data,data);
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
			payload.sauts = [];
			for (var i=0;i<self.data.length;i++)
				payload.sauts.push(rectToData(self.data[i]));
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.sauts.length;i++)
				self.data.push(dataToRect(payload.sauts[i]));
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleRect(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateRect(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipRect(self.data[i], imgSize,axis);
		}
	},
	"boosts": {
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				var boxSize = {w:iData.w,h:iData.h};
				updateBoxSize(self.state.point,boxSize);
				self.state.boxSize = boxSize;
				self.click(self,iData,{});
			}
			boostSizeChanged();
		},
		"click" : function(self,data,extra) {
			if (extra.oob)
				return;
			self.move(self,data,extra);
			data.w = self.state.boxSize.w;
			data.h = self.state.boxSize.h;
			storeHistoryData(self.data);
			self.data.push(data);
			var box = self.state.point;
			box.classList.remove("noclick");
			box.oncontextmenu = function(e) {
				hideBox(self.state.point,self.state.boxSize);
				return showContextOnElt(e,box,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						moveBox(box,data,data);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						$editor.removeChild(box);
						storeHistoryData(self.data);
						removeFromArray(self.data,data);
					}
				}]);
			};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
		},
		"move" : function(self,point,extra) {
			setBoxPosRound(self.state.point,point,self.state.boxSize);
		},
		"save" : function(self,payload) {
			payload.accelerateurs = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var iPayload = pointToData(iData);
				iPayload[0] = Math.round(iPayload[0]-iData.w/2);
				iPayload[1] = Math.round(iPayload[1]-iData.h/2);
				if (iData.w != 8 || iData.h != 8) {
					iPayload[2] = iData.w;
					iPayload[3] = iData.h;
				}
				payload.accelerateurs.push(iPayload);
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.accelerateurs.length;i++) {
				var iPayload = payload.accelerateurs[i];
				var iData = dataToPoint(iPayload);
				if (iPayload[2] && iPayload[3]) {
					iData.w = iPayload[2];
					iData.h = iPayload[3];
				}
				else {
					iData.w = 8;
					iData.h = 8;
				}
				iData.x += Math.floor(iData.w/2);
				iData.y += Math.floor(iData.h/2);
				self.data.push(iData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleBox(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateBox(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipBox(self.data[i], imgSize,axis);
		}
	},
	"decor": {
		"init" : function(self) {
			self.data = {};
		},
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var type = document.getElementById("decor-selector").getValue();
			var autoSelectType;
			if (!type) {
				autoSelectType = true;
				for (type in self.data)
					;
			}
			else
				autoSelectType = false;
			if (type) {
				var positions = self.data[type];
				self.data[type] = [];
				self.state.type = type;
				if (positions) {
					for (var i=0;i<positions.length;i++)
						self.click(self,positions[i],{});
					if (autoSelectType)
						document.getElementById("decor-selector").setValue(self.state.type);
				}
			}
		},
		"click" : function(self,point,extra) {
			if (extra.oob)
				return;
			if (!self.state.type)
				alert(language ? "Please select a decor type first":"Sélectionnez un type de décor avant de commencer");
			self.move(self,point,extra);
			storeHistoryData(self.data);
			self.data[self.state.type].push(point);
			var box = self.state.point;
			box.classList.remove("noclick");
			box.oncontextmenu = function(e) {
				hideBox(self.state.point,self.state.boxSize);
				return showContextOnElt(e,box,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						moveBox(box,point,self.state.boxSize);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						$editor.removeChild(box);
						storeHistoryData(self.data);
						removeFromArray(self.data[self.state.type],point);
					}
				}]);
			};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
		},
		"move" : function(self,point,extra) {
			setBoxPos(self.state.point,point,self.state.boxSize);
		},
		"save" : function(self,payload) {
			var selfData = self.data;
			payload.decor = {};
			for (var type in selfData) {
				var decorsData = selfData[type];
				if (decorsData.length) {
					payload.decor[type] = [];
					for (var i=0;i<decorsData.length;i++)
						payload.decor[type].push(pointToData(decorsData[i]));
				}
			}
		},
		"restore" : function(self,payload) {
			var selfData = self.data;
			for (var type in payload.decor) {
				selfData[type] = [];
				var decorsData = payload.decor[type];
				for (var i=0;i<decorsData.length;i++)
					selfData[type].push(dataToPoint(decorsData[i]));
			}
		},
		"rescale" : function(self, scale) {
			var selfData = self.data;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++)
					rescaleBox(decorsData[i], scale);
			}
		},
		"rotate" : function(self, orientation) {
			var selfData = self.data;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++)
					rotateBox(decorsData[i], imgSize,orientation);
			}
		},
		"flip" : function(self, axis) {
			var selfData = self.data;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++)
					flipBox(decorsData[i], imgSize,axis);
			}
		}
	},
	"options": {
		"init" : function(self) {
			self.data = {
				bg_img: 0,
				music: 1,
				out_color: {r:255,g:255,b:255}
			};
		},
		"resume" : function(self) {
			applyBgSelector();
			applyMusicSelector();
			document.body.classList.add("setting-preview");
			applyColorSelector();
		},
		"click" : function() {
		},
		"move": function() {
		},
		"exit": function(self) {
			document.body.classList.remove("setting-preview");
			document.body.style.backgroundColor = "";
		},
		"save" : function(self,payload) {
			payload.main.bgimg = self.data.bg_img;
			payload.main.music = self.data.music;
			if (self.data.youtube)
				payload.main.youtube = self.data.youtube;
			payload.main.bgcolor = [self.data.out_color.r,self.data.out_color.g,self.data.out_color.b];
		},
		"restore" : function(self,payload) {
			self.data.bg_img = payload.main.bgimg;
			self.data.music = payload.main.music;
			if (payload.main.youtube)
				self.data.youtube = payload.main.youtube;
			self.data.out_color = {r:payload.main.bgcolor[0],g:payload.main.bgcolor[1],b:payload.main.bgcolor[2]};
		}
	}
};