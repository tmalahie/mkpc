
var editorTools = {
	"start": {
		"init" : function(self) {
			self.data = [];
		},
		"resume" : function(self) {
			self.state.orientation = 2;
			self.state.arrow = createArrowNode({x:-10,y:-10},self.state.orientation);
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.arrow.rotate(iData.orientation);
				self.state.orientation = iData.orientation;
				self.click(self,iData,{});
			}
		},
		"click" : function(self,point,extra) {
			if (extra.oob)
				return;
			var arrow = self.state.arrow;
			if (!arrow)
				return;
			arrow.move(point);
			storeHistoryData(self.data);
			var data = deepCopy(point);
			data.orientation = self.state.orientation;
			self.data.push(data);
			var lastRotateTime = 0;
			arrow.origin.classList.add("hover-toggle");
			arrow.origin.onclick = function(e) {
				if (e) e.stopPropagation();
				var nRotateTime = new Date().getTime();
				if (nRotateTime > lastRotateTime+2500)
					storeHistoryData(self.data);
				lastRotateTime = nRotateTime;
				data.orientation = (data.orientation+1)%4;
				self.state.orientation = data.orientation;
				arrow.rotate(data.orientation);
				if (self.state.arrow)
					self.state.arrow.rotate(data.orientation);
			};
			function deleteItem() {
				$editor.removeChild(arrow.origin);
				var lines = arrow.lines;
				for (var i=0;i<lines.length;i++)
					$editor.removeChild(lines[i]);
				storeHistoryData(self.data);
				removeFromArray(self.data,data);
				if (!self.state.arrow)
					addArrow();
			}
			function addArrow() {
				var firstNode = $editor.firstChild;
				var nArrow = createArrowNode({x:-10,y:-10},self.state.orientation,false);
				for (var i=0;i<nArrow.lines.length;i++)
					$editor.insertBefore(nArrow.lines[i],firstNode);
				$editor.insertBefore(nArrow.origin,firstNode);
				self.state.arrow = nArrow;
			}
			arrow.origin.oncontextmenu = function(e) {
				return showContextOnElt(e,arrow.origin, [{
					text: (language ? "Rotate":"Pivoter"),
					click: function() {
						arrow.origin.onclick();
					}
				}, {
					text: (language ? "Move":"Déplacer"),
					click: function() {
						if (self.state.arrow)
							self.state.arrow.move({x:-10,y:-10});
						moveArrow(arrow,data);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						deleteItem();
					}
				}]);
			};
			if (self.data.length < 8)
				addArrow();
			else
				delete self.state.arrow;
		},
		"move" : function(self,point,extra) {
			if (self.state.arrow)
				self.state.arrow.move(point);
		},
		"round_on_pixel" : function(self) {
			return true;
		},
		"save" : function(self,payload) {
			var payloadStart = [];
			for (var i=0;i<self.data.length;i++) {
				var iPoint = self.data[i];
				var iData = pointToData(iPoint);
				iData[2] = iPoint.orientation;
				payloadStart.push(iData);
			}
			payload.main.startposition = payloadStart;
		},
		"restore" : function(self,payload) {
			var payloadStart = payload.main.startposition;
			for (var i=0;i<payloadStart.length;i++) {
				var iData = payloadStart[i];
				var iPoint = dataToPoint(iData);
				iPoint.orientation = iData[2];
				self.data.push(iPoint);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescalePoint(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotatePoint(iData, imgSize,orientation);
				iData.orientation = (iData.orientation + 4-orientation/90)%4;
			}
		},
		"flip": function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipPoint(iData, imgSize,axis);
				if ((iData.orientation%2) == (axis.coord=="x" ? 1:0))
					iData.orientation = (iData.orientation+2)%4;
			}
		}
	},
	"aipoints": {
		"init" : function(self) {
			self.data = {
				points:[],
				links:{}
			};
		},
		"resume" : function(self) {
			self.state.point = createCircle({x:-1,y:-1,r:0.5});
			self.state.point.classList.add("noclick");
			var points = self.data.points;
			var links = self.data.links;
			self.data = {
				points:[],
				links:{}
			};
			self.uuid = 0;
			self.state.links = {};
			var nodes = {};
			for (var i=0;i<points.length;i++) {
				self.click(self,points[i],{});
				nodes[points[i].uuid] = self.state.lastNode;
			}
			for (var uuid1 in links) {
				for (var uuid2 in links[uuid1]) {
					if (links[uuid1][uuid2]) {
						nodes[uuid1].circle.onclick();
						nodes[uuid2].circle.onclick();
					}
				}
			}
		},
		"click" : function(self,point,extra) {
			if (self.state.link) {
				$editor.removeChild(self.state.link.line);
				self.state.link.node.circle.classList.remove("dark");
				delete self.state.link;
				$toolbox.classList.remove("hiddenbox");
				self.onundo = null;
				self.onredo = null;
				return;
			}
			storeHistoryData(self.data);
			var data = deepCopy(point);
			data.uuid = self.uuid;
			self.data.points.push(data);
			var node = createPolygonNode({x:data.x,y:data.y,r:5});
			node.uuid = self.uuid;
			self.uuid++;
			self.state.links[node.uuid] = {};
			self.data.links[node.uuid] = {};
			node.circle.classList.add("hover-toggle");
			node.circle.onmouseover = function(e) {
				if (self.state.link)
					self.state.link.line.classList.add("dark");
			};
			node.circle.onmouseout = function(e) {
				if (self.state.link)
					self.state.link.line.classList.remove("dark");
			};
			node.circle.onclick = function(e) {
				if (e) e.stopPropagation();
				if (self.state.link) {
					var node0 = self.state.link.node;
					var link = self.state.links[node0.uuid][node.uuid];
					if (link) {
						storeHistoryData(self.data);
						delete self.data.links[node0.uuid][node.uuid];
						delete self.data.links[node.uuid][node0.uuid];
						var line = link.line;
						$editor.removeChild(line);
						delete self.state.links[node0.uuid][node.uuid];
						delete self.state.links[node.uuid][node0.uuid];
						$editor.removeChild(self.state.link.line);
					}
					else {
						if (node0 != node) {
							var line = self.state.link.line;
							storeHistoryData(self.data);
							self.data.links[node0.uuid][node.uuid] = 1;
							self.data.links[node.uuid][node0.uuid] = 0;
							moveLine(line,null,data);
							line.classList.remove("dark");
							var newLink = {node1:node0,node2:node,line:self.state.link.line};
							self.state.links[node0.uuid][node.uuid] = newLink;
							self.state.links[node.uuid][node0.uuid] = newLink;
							line.oncontextmenu = function(e) {
								e.stopPropagation();
								line.classList.add("dark");
								showContextMenu(e,[{
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										storeHistoryData(self.data);
										delete self.data.links[node0.uuid][node.uuid];
										delete self.data.links[node.uuid][node0.uuid];
										$editor.removeChild(line);
										delete self.state.links[node0.uuid][node.uuid];
										delete self.state.links[node.uuid][node0.uuid];
									}
								}],function() {
									line.classList.remove("dark");
								});
								return false;
							};
						}
					}
					self.state.link.node.circle.classList.remove("dark");
					$toolbox.classList.remove("hiddenbox");
					self.onundo = null;
					self.onredo = null;
					delete self.state.link;
				}
				else {
					var line = createLine(data,data,false);
					line.classList.add("bordered");
					addZoomListener(line, function() {
						this.setAttribute("stroke-width", 5/zoomLevel);
					});
					$editor.insertBefore(line,$editor.firstChild);
					self.state.link = {
						point: data,
						node: node,
						line: line
					};
					node.circle.classList.add("dark");
					$toolbox.classList.add("hiddenbox");
					self.onundo = function() {
						self.click(self);
					};
					self.onredo = function() {
					};
				}
			};
			node.circle.oncontextmenu = function(e) {
				if (self.state.link) {
					node.circle.onclick(e);
					return false;
				}
				return showContextOnElt(e,node.circle,[{
					text: (language ? "Move":"Déplacer"),
					click:function() {
						var linesData = [[],[]];
						var lines = self.state.links[data.uuid];
						for (var uuid in lines) {
							if (lines[uuid].node1 == node)
								linesData[0].push(lines[uuid].line);
							else if (lines[uuid].node2 == node)
								linesData[1].push(lines[uuid].line);
						}
						setCirclePos(self.state.point, {x:-1,y:-1});
						moveNode(node,data,linesData);
					}
				}, {
					text: (language ? "Delete":"Supprimer"),
					click:function() {
						storeHistoryData(self.data);
						removeFromArray(self.data.points,data);
						$editor.removeChild(node.circle);
						$editor.removeChild(node.center);
						var lines = self.state.links[data.uuid];
						for (var uuid in lines) {
							$editor.removeChild(lines[uuid].line);
							delete self.state.links[uuid][data.uuid];
							delete self.data.links[uuid][data.uuid];
						}
						delete self.state.links[data.uuid];
						delete self.data.links[data.uuid];
					}
				}]);
			};
			self.state.lastNode = node;
		},
		"move" : function(self,point,extra) {
			if (self.state.link)
				moveLine(self.state.link.line,null,point);
			else
				setNodePos(self.state.point,point);
		},
		"round_on_pixel" : function(self) {
			return true;
		},
		"save" : function(self,payload) {
			payload.aipoints = [];
			var autoIncArr = {};
			for (var i=0;i<self.data.points.length;i++) {
				var iPoint = self.data.points[i];
				payload.aipoints.push([0,iPoint.x,iPoint.y]);
				autoIncArr[self.data.points[i].uuid] = i;
			}
			var links = self.data.links;
			for (var uuid1 in links) {
				for (var uuid2 in links[uuid1]) {
					if (links[uuid1][uuid2])
						payload.aipoints.push([1,autoIncArr[uuid1],autoIncArr[uuid2]]);
				}
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.aipoints.length;i++) {
				var iData = payload.aipoints[i];
				if (iData[0]) {
					self.data.links[iData[1]][iData[2]] = 1;
					self.data.links[iData[2]][iData[1]] = 0;
				}
				else {
					self.data.points.push({x:iData[1],y:iData[2],uuid:i});
					self.data.links[i] = {};
				}
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.points.length;i++)
				rescalePoint(self.data.points[i],scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.points.length;i++)
				rotatePoint(self.data.points[i],imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.points.length;i++)
				flipPoint(self.data.points[i],imgSize,axis);
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
				music: 9,
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