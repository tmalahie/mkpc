
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
				data.orientation = Math.round(data.orientation+1)%4;
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
				var nArrow = createArrowNode({x:-10,y:-10},self.state.orientation,{append:false});
				for (var i=0;i<nArrow.lines.length;i++)
					$editor.insertBefore(nArrow.lines[i],firstNode);
				$editor.insertBefore(nArrow.origin,firstNode);
				self.state.arrow = nArrow;
			}
			arrow.origin.oncontextmenu = function(e) {
				return showContextOnElt(e,arrow.origin, [{
					text: (language ? "Rotate 90°":"Rotation 90°"),
					click: function() {
						arrow.origin.onclick();
					}
				}, {
					text: (language ? "Rotate custom...":"Rotation libre..."),
					click: function() {
						rotateArrowNode(arrow,data,data, {
							on_apply: function(nOrientation) {
								self.state.orientation = nOrientation;
								if (self.state.arrow)
									self.state.arrow.rotate(nOrientation);
							}
						});
					}
				}, {
					text: (language ? "Move":"Déplacer"),
					click: function() {
						if (self.state.arrow)
							self.state.arrow.move({x:-10,y:-10});
						moveArrowNode(arrow,data);
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
		},
		"disableOverride": function() {
			return language ? "Start positions can only be defined at default override" : "Les positions de départ ne peuvent être definies qu'au modificateur par défaut";
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
					if (links[uuid1][uuid2].dir) {
						nodes[uuid1].circle.onclick();
						nodes[uuid2].circle.onclick();
						if (links[uuid1][uuid2].arrow) {
							self.state.links[uuid1][uuid2].line.oneway();
						}
					}
				}
			}
		},
		"click" : function(self,point,extra) {
			if (self.state.link) {
				removeRouteLink(self.state.link);
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
						removeRouteLink(link);
						delete self.state.links[node0.uuid][node.uuid];
						delete self.state.links[node.uuid][node0.uuid];
						$editor.removeChild(self.state.link.line);
					}
					else {
						if (node0 != node) {
							var line = self.state.link.line;
							storeHistoryData(self.data);
							self.data.links[node0.uuid][node.uuid] = {dir:1};
							self.data.links[node.uuid][node0.uuid] = {dir:0};
							moveLine(line,null,data);
							line.classList.remove("dark");
							var newLink = {node1:node0,node2:node,line:line};
							self.state.links[node0.uuid][node.uuid] = newLink;
							self.state.links[node.uuid][node0.uuid] = newLink;
							line.oneway = function() {
								self.data.links[node0.uuid][node.uuid].arrow = 1;
								var pt1 = self.data.points.find(function(pt) {return pt.uuid == node0.uuid});
								var pt2 = self.data.points.find(function(pt) {return pt.uuid == node.uuid});
								var orientation = Math.atan2(pt2.x-pt1.x,pt2.y-pt1.y)*2/Math.PI || 0;
								newLink.arrow = createArrowNode({x:(pt1.x+pt2.x)/2,y:(pt1.y+pt2.y)/2},orientation,{l:4,d:8,origin:false});
								for (var i=0;i<newLink.arrow.lines.length;i++)
									newLink.arrow.lines[i].classList.add("noclick");
							};
							line.oncontextmenu = function(e) {
								e.stopPropagation();
								line.classList.add("dark");
								var ctxMenuItems = [{
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										storeHistoryData(self.data);
										delete self.data.links[node0.uuid][node.uuid];
										delete self.data.links[node.uuid][node0.uuid];
										removeRouteLink(newLink);
										delete self.state.links[node0.uuid][node.uuid];
										delete self.state.links[node.uuid][node0.uuid];
									}
								}];
								if (newLink.arrow) {
									ctxMenuItems.unshift({
										text:(language ? "✔ Make 1-way":"✔ Unidirectionnel"),
										click:function() {
											storeHistoryData(self.data);
											delete self.data.links[node0.uuid][node.uuid].arrow;
											var lines = newLink.arrow.lines;
											for (var i=0;i<lines.length;i++)
												$editor.removeChild(lines[i]);
											delete newLink.arrow;
										}
									}, {
										text:(language ? "Reverse ↗":"Inverser ↗"),
										click:function() {
											storeHistoryData(self.data);
											self.data.links[node0.uuid][node.uuid] = {dir:0};
											self.data.links[node.uuid][node0.uuid] = {dir:1};
											var node1 = node0;
											node0 = node;
											node = node1;
											var lines = newLink.arrow.lines;
											for (var i=0;i<lines.length;i++)
												$editor.removeChild(lines[i]);
											line.oneway();
											node = node0;
											node0 = node1;
										}
									});
								}
								else {
									ctxMenuItems.unshift({
										text:(language ? "Make 1-way":"Unidirectionnel"),
										click:function() {
											storeHistoryData(self.data);
											line.oneway();
										}
									});
								}
								showContextMenu(e,ctxMenuItems,function() {
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
								linesData[0].push(lines[uuid]);
							else if (lines[uuid].node2 == node)
								linesData[1].push(lines[uuid]);
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
							removeRouteLink(lines[uuid]);
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
					if (links[uuid1][uuid2].dir) {
						var iLink = [1,autoIncArr[uuid1],autoIncArr[uuid2]];
						if (links[uuid1][uuid2].arrow)
							iLink.push(1);
						payload.aipoints.push(iLink);
					}
				}
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.aipoints.length;i++) {
				var iData = payload.aipoints[i];
				if (iData[0]) {
					self.data.links[iData[1]][iData[2]] = {dir:1,arrow:iData[3]};
					self.data.links[iData[2]][iData[1]] = {dir:0};
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
	"walls": commonTools["walls"],
	"offroad": commonTools["offroad"],
	"holes": commonTools["holes"],
	"items": commonTools["items"],
	"jumps": commonTools["jumps"],
	"boosts": commonTools["boosts"],
	"decor": commonTools["decor"],
	"cannons": commonTools["cannons"],
	"teleports": commonTools["teleports"],
	"mobiles": commonTools["mobiles"],
	"elevators": commonTools["elevators"],
	"options": commonTools["options"]
};

function removeRouteLink(link) {
	$editor.removeChild(link.line);
	if (link.arrow) {
		var lines = link.arrow.lines;
		for (var i=0;i<lines.length;i++)
			$editor.removeChild(lines[i]);
	}
}