/**
 *	Tipmage
 *	version 1.0
 *
 *	Tipmage is a javascript class aimed at creating and managing tooltips
 *	(or "notes") over images.
 *	Tipmage makes it possible to mark rectangular portions of an image and
 *	attach a description to each one of them. The description will be shown
 *	as a tooltip when the mouse is over the right section of the image.
 *	The class can work in two ways: in _normal_ mode it just shows the tooltips,
 *	while in _edit_ mode it also allows the user to edit them.
 *	Tipmage supports the use of special callback functions to perform operations
 *	related to the editing of a tooltip (for example AJaX calls to access a database).
 *	An external CSS stylesheet allows to customize the appearance of the user interface.
 *
 *	See: http://www.simbul.net/stuff/tipmage.php
 *
 *	Copyright (C) 2005 by
 *	Alessandro Morandi
 *	www.simbul.net
 *
 *	Feel free to redistribute under the GPL
 *	http://www.gnu.org/copyleft/gpl.html
 */

/**
 *	Class constructor
 *	@param	id	Id of the base image for the tooltips
 *	@param	isEditable	Wether the tooltips will be editable or not
 */
function Tipmage (id,isEditable) {
	
	// CONSTANTS (customize as you wish)
	Tipmage.prototype._tooltipHorDist = 10;			// Horizontal distance of the tooltip from the rectangle (in pixels)
	Tipmage.prototype._tooltipVerDist = 10;			// Vertical distance of the tooltip from the rectangle (in pixels)
	Tipmage.prototype._cornerWidth = 8;				// Corner handle width (in pixels)
	Tipmage.prototype._cornerHeight = 8;			// Corner handle height (in pixels)
	Tipmage.prototype._rectModWidth = 50;			// Default width of the editing rectangle
	Tipmage.prototype._rectModHeight = 50;			// Default height of the editing rectangle
	Tipmage.prototype._hideTimeout = 400;			// Timeout for hiding tooltips (in milliseconds)
	Tipmage.prototype._hideInitialTimeout = 2000;	// Timeout for hiding rectangles on image loading (in milliseconds)

	// FIELDS & FLAGS (do not touch!)
	this.imageId = id;				// ID of the target image
	this.isEditable = isEditable;	// Whether the tooltips are editable or not
	this.tooltipNextId = 1;			// Next numeric ID for tooltips
	this.modNumId = 0;				// Numeric ID of the tooltip being modified (0: none)
	this.mouseOverNumId = 0;		// Numeric ID of the rect with mouse over
	this.isMoving = 0;				// Whether the user is moving rectangles or corners
	this.overContainer = 0;			// Whether the cursor is over the main container
}

Tipmage.prototype = {
	
	/**
	 *	Start the Tipmage engine, creating all the needed elements
	 *	in the Document Object Model (DOM)
	 */
	'startup' : function () {
		var image = this.getEl(this.imageId);
		var myself = this;
		
		// Create a new container *around* the image
		var tmContainer = myself.createElement('div','tmContainer','tmContainer');
		tmContainer.style.display = 'block';
		tmContainer.style.padding = '0px';
		tmContainer.style.margin = '0px';
		tmContainer.style.clear = 'both';	
	
		var father = image.parentNode;
		var siblings = father.childNodes;	// Get all the <img> siblings
		for (i=0; i<siblings.length; i++) {
			if (siblings[i].id==this.imageId) {
				father.replaceChild(tmContainer,siblings[i]);
				break;	// We don't know how big the document may be...
			}
		}
	
		// Create the tooltips container
		var tmTooltips = myself.createElement('div','tmTooltips','tmTooltips');
		tmTooltips.style.position = 'absolute';
		tmTooltips.style.textAlign = 'left';
		tmContainer.appendChild(tmTooltips);
		
		tmContainer.appendChild(image);	// NOW we can put back the image
		
		// Bind size recalculation on page load (otherwise the image properties would be wrong)
		myself.addLoadEvent(
			function() {
				tmContainer.style.width = myself.num2size(image.width);
				tmContainer.style.height = myself.num2size(image.height);
				tmTooltips.style.width = myself.num2size(image.width);
				tmTooltips.style.height = myself.num2size(image.height);
			}
		);
		
		// Assign the mouse function to the container
		tmContainer.onmouseover = function (e) {
			if (!myself.isMoving) {
				myself.overContainer = 1;
				tmTooltips.style.visibility = 'visible';
			}
		};
		tmContainer.onmouseout = function (e) {
			if (!myself.isMoving) {
				myself.overContainer = 0;
				tmTooltips.style.visibility = 'hidden';
			}
		};
		
		// Show the rects for the first time and then hide them
		setTimeout(
			function(){
				if (!myself.overContainer) {
					tmTooltips.style.visibility = 'hidden';
				}
			},
			myself._hideInitialTimeout);
		
		// Create all the elements for editing
		if (this.isEditable) {
			// Create the rectangle
			var tmRectMod = myself.createElement('div','tmRectMod','tmRectMod');
			tmRectMod.style.position = 'absolute';
			tmRectMod.style.zIndex = '180';
			tmRectMod.style.left = '0px';
			tmRectMod.style.top = '0px';
			tmRectMod.style.width = myself.num2size(myself._rectModWidth);
			tmRectMod.style.height = myself.num2size(myself._rectModHeight);
			tmRectMod.style.visibility = 'hidden';
			myself.modNumId = 0;
			tmTooltips.appendChild(tmRectMod);
			
			// Create the contrast rectangle
			var tmRectContrastMod = myself.createElement('div','tmRectContrastMod','tmRectContrastMod');
			tmRectContrastMod.style.position = 'absolute';
			tmRectContrastMod.style.zIndex = '181';
			tmRectContrastMod.style.width = '100%';
			tmRectContrastMod.style.height = '100%';
			tmRectMod.appendChild(tmRectContrastMod);
			
			// Create the inner rectangle for mouseOver
			var tmRectInsideMod = myself.createElement('div','tmRectInsideMod','tmRectInsideMod');
			tmRectInsideMod.style.position = 'absolute';
			tmRectInsideMod.style.zIndex = '190';
			tmRectInsideMod.style.width = '100%';
			tmRectInsideMod.style.height = '100%';
			tmRectInsideMod.style.cursor = 'move';
			tmRectInsideMod.style.background = '#FFF';
			tmRectInsideMod.style.opacity = '0';
			tmRectInsideMod.style.filter = 'alpha(opacity=0)';	// Needed for IE
			tmRectMod.appendChild(tmRectInsideMod);
			
			// Create the tooltip
			var tmTooltipMod = myself.createElement('div','tmTooltipMod','tmTooltipMod');
			tmTooltipMod.style.position = 'absolute';
			tmTooltipMod.style.zIndex = '140';
			tmTooltipMod.style.left = myself.num2size(50+myself._tooltipHorDist);
			tmTooltipMod.style.top = myself.num2size(50+myself._tooltipVerDist);
			tmTooltipMod.style.width = '220px';
			tmTooltipMod.style.visibility = 'hidden';
			tmTooltips.appendChild(tmTooltipMod);
			
			// Create the form
			var tmForm = myself.createElement('form','tmForm','tmForm');
			tmForm.style.zIndex = '160';
			tmTooltipMod.appendChild(tmForm);
			
			// Create the text area
			var tmTextArea = myself.createElement('textArea','tmTextArea','tmTextArea');
			tmTextArea.style.zIndex = '170';
			tmForm.appendChild(tmTextArea);
			
			// Create the buttons
			var tmButtonSave = myself.createElement('input','tmButton','tmButtonSave');
			tmButtonSave.type = 'button';
			tmButtonSave.value = 'Save';
			tmForm.appendChild(tmButtonSave);
			var tmButtonCancel = myself.createElement('input','tmButton','tmButtonCancel');
			tmButtonCancel.type = 'button';
			tmButtonCancel.value = 'Cancel';
			tmForm.appendChild(tmButtonCancel);
			var tmButtonDelete = myself.createElement('input','tmButton','tmButtonDelete');
			tmButtonDelete.type = 'button';
			tmButtonDelete.value = 'Delete';
			tmForm.appendChild(tmButtonDelete);
			
			// Create the handles
			tmCornerNW = myself.createCorner('nw');
			tmRectMod.appendChild(tmCornerNW);
			tmCornerNE = myself.createCorner('ne');
			tmRectMod.appendChild(tmCornerNE);
			tmCornerSE = myself.createCorner('se');
			tmRectMod.appendChild(tmCornerSE);
			tmCornerSW = myself.createCorner('sw');
			tmRectMod.appendChild(tmCornerSW);
			
			// Assign the mouse function to the inner rectangle
			tmRectInsideMod.onmousedown = function(e) {
				myself.disableSelect();
				myself.isMoving = 1;
				var rectStartX = myself.size2num(tmRectMod.style.left);
				var rectStartY = myself.size2num(tmRectMod.style.top);
				var cursorStart = myself.getCursorPosition(e);
				
				tmTooltipMod.style.visibility = 'hidden';
				
				// Avoid event propagation
				if (window.event) {
					window.event.cancelBubble = true;
					window.event.returnValue = false;
				}
				if (e && e.preventDefault) {
					e.preventDefault();
				}
	
				document.onmouseup = function(e) {
					myself.isMoving = 0;
					myself.enableSelect();
					document.onmousemove = null;
					document.onmouseup = null;
					myself.showTooltipMod();
				};
				document.onmousemove = function(e) {
					var cursorNow = myself.getCursorPosition(e);
					var imageSize = myself.getImageSize();
					var left = cursorNow[0] - cursorStart[0] + rectStartX;
					var top = cursorNow[1] - cursorStart[1] + rectStartY;
					if (left >= 0 && left <= imageSize[0] - myself.size2num(tmRectMod.style.width)) {
						tmRectMod.style.left = myself.num2size(left);
					}
					if (top >= 0 && top <= imageSize[1] - myself.size2num(tmRectMod.style.height)) {
						tmRectMod.style.top = myself.num2size(top);
					}
				};
			};
			
			// Assign the mouse function to the buttons
			tmButtonSave.onclick = function(e) {
				if (myself.modNumId==myself.tooltipNextId) {
					myself.onInsert(myself.modNumId,
									myself.size2num(tmRectMod.style.left),
									myself.size2num(tmRectMod.style.top),
									myself.size2num(tmRectMod.style.width),
									myself.size2num(tmRectMod.style.height),
									tmTextArea.value);
				} else {
					myself.onUpdate(myself.modNumId,
									myself.size2num(tmRectMod.style.left),
									myself.size2num(tmRectMod.style.top),
									myself.size2num(tmRectMod.style.width),
									myself.size2num(tmRectMod.style.height),
									tmTextArea.value);
				}
				myself.stopEditMode('save');
			};
			tmButtonCancel.onclick = function (e) {
				myself.stopEditMode('cancel');
			};
			tmButtonDelete.onclick = function (e) {
				myself.onDelete(myself.modNumId,
								myself.size2num(tmRectMod.style.left),
								myself.size2num(tmRectMod.style.top),
								myself.size2num(tmRectMod.style.width),
								myself.size2num(tmRectMod.style.height),
								tmTextArea.value);
				myself.stopEditMode('delete');
			};
			
			// Assign the mouse function to the image
			tmContainer.ondblclick = function (e) {
				// Make sure the user didn't click on strange things...
				var target = myself.getEventTarget(myself.getEvent(e));
				if ((target.id==myself.imageId || target.id=='tmTooltips') && !myself.isLocked()) {
					myself.startEditMode(e);
					/*	Note: this is needed to "decouple" the function startEditMode from the
						element tmContainer. The more clean way to write it, which is:
						tmContainer.ondblclick = myself.startEditMode;
						is broken, because every reference to "this" in the startEditMode method
						would refer to the tmContainer element instead of the Tipmage object.
					*/
				}
			};
		}
	},
	
	/**
	 *	Set up a new tooltip
	 *	@param	posx		X coordinate of the tooltip rectangle after editing
	 *	@param	posy		Y coordinate of the tooltip rectangle after editing
	 *	@param	width		Width of the tooltip rectangle after editing
	 *	@param	height		Height of the tooltip rectangle after editing
	 *	@param	text		Text contained in the tooltip after editing
	 *	@param	identifier	(Optional) Identifier for this tooltip (e.g. in a database)
	 *	@return	numId of the new tooltip
	 */
	'setTooltip' : function (posx,posy,width,height,text,identifier) {
		var myself = this;
		var tooltips = myself.getEl('tmTooltips');
		
		// If an identifier is provided, use it and update the NextId counter.
		// Otherwise, just use the next available id.
		if (identifier) {
			var numId = identifier;
			if (myself.tooltipNextId < identifier) {
				myself.tooltipNextId = identifier;
			}
		} else {
			var numId = myself.tooltipNextId;
		}
		
		// Create the rectangle
		var tmRect = myself.createElement('div','tmRect','tmRect'+numId);
		tmRect.style.position = 'absolute';
		tmRect.style.zIndex = '120';
		tmRect.style.left = myself.num2size(posx);
		tmRect.style.top = myself.num2size(posy);
		tmRect.style.width = myself.num2size(width);
		tmRect.style.height = myself.num2size(height);
		tooltips.appendChild(tmRect);
		
		// Create the contrast rectangle
		var tmRectContrast = myself.createElement('div','tmRectContrast','tmRectContrast'+numId);
		tmRectContrast.style.position = 'absolute';
		tmRectContrast.style.zIndex = '121';
		tmRectContrast.style.width = '100%'
		tmRectContrast.style.height = '100%'
		tmRect.appendChild(tmRectContrast);
		
		// Create the inner rectangle for mouseOver
		var tmRectInside = myself.createElement('div','tmRectInside','tmRectInside'+numId);
		tmRectInside.style.position = 'absolute';
		tmRectInside.style.zIndex = '130';
		tmRectInside.style.width = '100%'
		tmRectInside.style.height = '100%'
		tmRectInside.style.background = '#FFF';
		tmRectInside.style.opacity = '0';
		tmRectInside.style.filter = 'alpha(opacity=0)';	// Needed for IE
		tmRect.appendChild(tmRectInside);
		
		// Create the tooltip
		var tmTooltip = myself.createElement('div','tmTooltip','tmTooltip'+numId);
		tmTooltip.style.position = 'absolute';
		tmTooltip.style.zIndex = '140';
		tmTooltip.style.left = myself.num2size(posx+width+myself._tooltipHorDist);
		tmTooltip.style.top = myself.num2size(posy+height+myself._tooltipVerDist);
		tmTooltip.style.width = myself.num2size(myself.calculateWidth(text));
		tmTooltip.style.visibility = 'hidden';
		tooltips.appendChild(tmTooltip);
	
		// Create the tooltip text
		var tmText = myself.createElement('p','tmText','tmText'+numId);
		tmText.style.zIndex = '150';
		tmText.innerHTML = text;
		tmTooltip.appendChild(tmText);
		
		// Assign the mouse functions to the inner rectangle
		tmRectInside.onmouseover = function(e) {
			if (myself.isLocked()) {
				return;
			}
			myself.mouseOverNumId = numId;
			tmRect.className='tmRectSelected';
			tmRectContrast.className='tmRectContrastSelected';
			tmRectContrast.style.height = tmRect.style.height;	// IE does not understand %...
			tmTooltip.style.visibility = 'visible';
			myself.hideTooltipsExcept(numId);
		};
		tmRectInside.onmouseout = function(e) {
			if (myself.isLocked()) {
				return;
			}
			myself.mouseOverNumId = 0;
			myself.hideTooltip(numId);
		};
		if (myself.isEditable) {
			tmRectInside.onclick = function(e) {
				if (myself.isLocked()) {
					return;
				}
				myself.startEditMode(e);
			};
		}
		
		// Assign the mouse functions to the tooltip
		tmTooltip.onmouseover = function(e) {
			if (myself.isLocked()) {
				return;
			}
			myself.mouseOverNumId = numId;
		};
		tmTooltip.onmouseout = function(e) {
			if (myself.isLocked()) {
				return;
			}
			myself.mouseOverNumId = 0;
			myself.hideTooltip(numId);
		};

		myself.tooltipNextId = numId+1;
		return numId;
	},
	
	/**
	 *	Hide a tooltip after a timeout expiration
	 *	@param	numId	Numeric id of the tooltip
	 */
	'hideTooltip' : function (numId) {
		var myself = this;
		
		setTimeout(
			function(){
				if (myself.mouseOverNumId==numId) return;
				myself.getEl('tmTooltip'+numId).style.visibility = 'hidden';
				var tmRect = myself.getEl('tmRect'+numId);
				tmRect.className='tmRect';
				var tmRectContrast = myself.getEl('tmRectContrast'+numId);
				tmRectContrast.className='tmRectContrast';
				tmRectContrast.style.height = tmRect.style.height;	// IE does not understand %...
			},
			myself._hideTimeout);
	},
	
	/**
	 *	Hide all the tooltips except the one specified
	 *	@param	numId	Numeric id of the tooltip that has to remain visible
	 */
	'hideTooltipsExcept' : function (numId) {
		var myself = this;
		var lastId = myself.tooltipNextId - 1;
		var elem = null;
		
		for (i=1; i<=lastId; i++) {
			var tmRect = myself.getEl('tmRect'+i);
			if (tmRect!=null) {
				if (i != numId) {
					tmRect.className = 'tmRect';
					var tmRectContrast = myself.getEl('tmRectContrast'+i);
					tmRectContrast.className = 'tmRectContrast';
					tmRectContrast.style.height = tmRect.style.height;	// IE does not understand %...
					myself.getEl('tmTooltip'+i).style.visibility = 'hidden';
				}
			}
		}
		
	},
	
	/**
	 *	Show the tooltip for editing operations after calculating its position
	 */
	'showTooltipMod' : function () {
		var myself = this;
		var tmTooltipMod = myself.getEl('tmTooltipMod');
		var tmRectMod = myself.getEl('tmRectMod');
		var tmTextArea = myself.getEl('tmTextArea');
		
		tmTooltipMod.style.left = myself.num2size(myself.size2num(tmRectMod.style.left) + myself.size2num(tmRectMod.style.width) + myself._tooltipHorDist);
		tmTooltipMod.style.top = myself.num2size(myself.size2num(tmRectMod.style.top) + myself.size2num(tmRectMod.style.height) + myself._tooltipVerDist);
		tmTooltipMod.style.visibility = 'visible';
		tmTextArea.focus();
		tmTextArea.select();
	},
	
	/**
	 *	Create a corner (handler) for the editing rectangle
	 *	@param	name	Name of the corner (nw,sw,se,ne)
	 */
	'createCorner' : function (name) {
		var myself = this;
		var corner = myself.createElement('div','tmCorner','tmCorner'+name.toUpperCase());
		corner.style.position = 'absolute';
		corner.style.width = myself.num2size(myself._cornerWidth);
		corner.style.height = myself.num2size(myself._cornerHeight);
		corner.style.zIndex = '200';
		corner.style.fontSize = '1px';
		corner.style.overflow = 'hidden';
		corner.style.cursor = name+'-resize';
		if (name.charAt(0)=='n') {
			corner.style.top='-1px';
		}
		if (name.charAt(0)=='s') {
			corner.style.bottom='-1px';
		}
		if (name.charAt(1)=='w') {
			corner.style.left='-1px';
		}
		if (name.charAt(1)=='e') {
			corner.style.right='-1px';
		}
		
		corner.onmousedown = function(e) {
			myself.disableSelect();
			myself.isMoving = 1;
			
			var tmRectMod = myself.getEl('tmRectMod');
			var tmRectInsideMod = myself.getEl('tmRectInsideMod');
			var tmRectContrastMod = myself.getEl('tmRectContrastMod');
			var tmTooltipMod = myself.getEl('tmTooltipMod');
			
			var rectStartX = myself.size2num(tmRectMod.style.left);
			var rectStartY = myself.size2num(tmRectMod.style.top);
			var rectStartWidth = myself.size2num(tmRectMod.style.width);
			var rectStartHeight = myself.size2num(tmRectMod.style.height);
			var cursorStart = myself.getCursorPosition(e);
			
			tmTooltipMod.style.visibility = 'hidden';
					
			// Avoid event propagation
			if (window.event) {
				window.event.cancelBubble = true;
				window.event.returnValue = false;
			}
			if (e && e.preventDefault) {
				e.preventDefault();
			}

			document.onmouseup = function(e) {
				myself.isMoving = 0;
				myself.enableSelect();
				document.onmousemove = null;
				document.onmouseup = null;
				myself.showTooltipMod();
			};
			document.onmousemove = function(e) {
				var cursorNow = myself.getCursorPosition(e);
				var imageSize = myself.getImageSize();
				var offsetX = cursorNow[0] - cursorStart[0];
				var offsetY = cursorNow[1] - cursorStart[1];

				if (name.charAt(0)=='n') {
					if (offsetY + rectStartY >= 0 && rectStartHeight - offsetY >= (myself._cornerHeight*2)) {
						tmRectMod.style.top = myself.num2size(offsetY + rectStartY);
						tmRectMod.style.height = myself.num2size(rectStartHeight - offsetY);
					}
				}
				if (name.charAt(0)=='s') {
					if (offsetY + rectStartY + rectStartHeight <= imageSize[1] && rectStartHeight + offsetY >= (myself._cornerHeight*2)) {
						tmRectMod.style.height = myself.num2size(rectStartHeight + offsetY);
					}
				}
				if (name.charAt(1)=='w') {
					if (offsetX + rectStartX >= 0 && rectStartWidth - offsetX >= (myself._cornerWidth*2)) {
						tmRectMod.style.left = myself.num2size(offsetX + rectStartX);
						tmRectMod.style.width = myself.num2size(rectStartWidth - offsetX);
					}
				}
				if (name.charAt(1)=='e') {
					if (offsetX + rectStartX + rectStartWidth <= imageSize[0] && rectStartWidth + offsetX >= (myself._cornerWidth*2)) {
						tmRectMod.style.width = myself.num2size(rectStartWidth + offsetX);
					}
				}
			};
		};

		return corner;
	},
	
	/**
	 *	Start edit mode for tooltips and rectangles
	 *	@param	e	The event that triggered this function
	 */
	'startEditMode' : function (e) {
		var evt = this.getEvent(e);
		var target = this.getEventTarget(evt);
		var myself = this;
		var tmRectMod = myself.getEl('tmRectMod');
		var tmRectContrastMod = myself.getEl('tmRectContrastMod');
		var tmRectInsideMod = myself.getEl('tmRectInsideMod');
		var tmTextArea = myself.getEl('tmTextArea');

		myself.mouseOverNumId = 0;
		if ((target.id=='tmTooltips' || target.id==this.imageId) && evt.type=='dblclick') {
			// Create a new tooltip
			myself.modNumId = myself.tooltipNextId;
			var pos = myself.getCursorPosition(evt);
			var size = myself.getImageSize();
			var tmContainer = myself.getEl('tmContainer');
			var offsetLeft = myself.getTotalOffsetLeft(tmContainer);
			var offsetTop = myself.getTotalOffsetTop(tmContainer);
			tmRectMod.style.left = myself.num2size(Math.min(pos[0] - offsetLeft, size[0] - myself._rectModWidth));
			tmRectMod.style.top = myself.num2size(Math.min(pos[1] - offsetTop, size[1] - myself._rectModHeight));
			tmRectMod.style.width = myself.num2size(myself._rectModWidth);
			tmRectMod.style.height = myself.num2size(myself._rectModHeight);
			tmTextArea.value = 'Insert text here';
			myself.getEl('tmButtonDelete').style.visibility = 'hidden';
		} else {
			var numId = target.id.split('tmRectInside').join('');
			// Edit an existing tooltip
			var tmRect = myself.getEl('tmRect'+numId);
			var tmTooltip = myself.getEl('tmTooltip'+numId);
			var tmText = myself.getEl('tmText'+numId);

			myself.modNumId = numId;
			
			tmRect.style.visibility = 'hidden';
			tmTooltip.style.visibility = 'hidden';
			
			tmRectMod.style.left = tmRect.style.left;
			tmRectMod.style.top = tmRect.style.top;
			tmRectMod.style.width = tmRect.style.width;
			tmRectMod.style.height = tmRect.style.height;
			tmTextArea.value = myself.br2nl(tmText.innerHTML);
			myself.getEl('tmButtonDelete').style.visibility = '';
		}
		tmRectMod.style.visibility = 'visible';
		myself.showTooltipMod();
		tmTextArea.focus();
		tmTextArea.select();
	},
	
	/**
	 *	Stop edit mode for tooltips and rectangles and perform the appropriate action
	 *	@param	action	The action that triggered this function (save, cancel, delete)
	 */
	'stopEditMode' : function (action) {
		var myself = this;
		var tmRectMod = myself.getEl('tmRectMod');
		var tmTooltipMod = myself.getEl('tmTooltipMod');
		var tmTextArea = myself.getEl('tmTextArea');
		
		if (myself.modNumId==myself.tooltipNextId && action=='save') {
			// Create a new tooltip
			myself.setTooltip(	myself.size2num(tmRectMod.style.left),
										myself.size2num(tmRectMod.style.top),
										myself.size2num(tmRectMod.style.width),
										myself.size2num(tmRectMod.style.height),
										tmTextArea.value);
		}
		var tmRect = myself.getEl('tmRect'+myself.modNumId);
		var tmRectContrast = myself.getEl('tmRectContrast'+myself.modNumId);
		var tmRectInside = myself.getEl('tmRectInside'+myself.modNumId);
		var tmTooltip = myself.getEl('tmTooltip'+myself.modNumId);
		var tmText = myself.getEl('tmText'+myself.modNumId);
		
		tmRectMod.style.visibility = 'hidden';
		tmTooltipMod.style.visibility = 'hidden';
		if (action=='save') {
			tmRect.style.left = tmRectMod.style.left ;
			tmRect.style.top = tmRectMod.style.top;
			tmRect.style.width = tmRectMod.style.width;
			tmRect.style.height = tmRectMod.style.height;
			tmRectContrast.style.width = tmRectMod.style.width;
			tmRectContrast.style.height = tmRectMod.style.height;
			tmTooltip.style.left = myself.num2size(myself.size2num(tmRect.style.left) + myself.size2num(tmRect.style.width) + myself._tooltipHorDist);
			tmTooltip.style.top = myself.num2size(myself.size2num(tmRect.style.top) + myself.size2num(tmRect.style.height) + myself._tooltipVerDist);
			tmText.innerHTML = myself.nl2br(tmTextArea.value);
			tmTooltip.style.width = myself.num2size(myself.calculateWidth(tmText.innerHTML));
			tmRect.style.visibility = '';
			tmTooltip.style.visibility = '';
			myself.hideTooltip(myself.modNumId);
		}
		if (action=='cancel' && tmRect) {			
			tmRect.style.visibility = '';
			tmTooltip.style.visibility = '';
			myself.hideTooltip(myself.modNumId);
		}
		if (action=='delete') {
			var tmTooltips = myself.getEl('tmTooltips');
			tmTooltips.removeChild(tmRect);
			tmTooltips.removeChild(tmTooltip);
		}

		myself.modNumId = 0;
	},
	
	/**
	 *	Return the status of the current image. When one of the rectangles is being
	 *	edited, the image is locked and other elements cannot be modified.
	 *	@return	True if the image is locked, false otherwise
	 */
	'isLocked' : function() {
		var myself = this;
		if (!this.isEditable || this.modNumId==0) {
			return false;
		} else {
			return true;
		}
	},
	
	/**
	 *	It can be used to save the tooltip in a database with AjAX.
	 *	@param	identifier	An identifier (ID) that may be useful for a database
	 *	@param	posx	X coordinate of the tooltip rectangle after editing
	 *	@param	posy		Y coordinate of the tooltip rectangle after editing
	 *	@param	width	Width of the tooltip rectangle after editing
	 *	@param	height	Height of the tooltip rectangle after editing
	 *	@param	text		Text contained in the tooltip after editing
	 */
	'onInsert' : function (identifier,posx,posy,width,height,text) {
		// do nothing - this is just a stub
	},
	
	/**
	 *	It can be used to update the tooltip in a database with AjAX.
	 *	@param	identifier	An identifier (ID) that may be useful for a database
	 *	@param	posx	X coordinate of the tooltip rectangle after editing
	 *	@param	posy		Y coordinate of the tooltip rectangle after editing
	 *	@param	width	Width of the tooltip rectangle after editing
	 *	@param	height	Height of the tooltip rectangle after editing
	 *	@param	text		Text contained in the tooltip after editing
	 */	
	'onUpdate' : function (identifier,posx,posy,width,height,text) {
		// do nothing - this is just a stub
	},
	
	/**
	 *	It can be used to delete the tooltip from a database with AjAX.
	 *	@param	identifier	An identifier (ID) that may be useful for a database
	 *	@param	posx	X coordinate of the tooltip rectangle after editing
	 *	@param	posy		Y coordinate of the tooltip rectangle after editing
	 *	@param	width	Width of the tooltip rectangle after editing
	 *	@param	height	Height of the tooltip rectangle after editing
	 *	@param	text		Text contained in the tooltip after editing
	 */
	'onDelete' : function (identifier,posx,posy,width,height,text) {
		// do nothing - this is just a stub
	},
	
	
	/**************************************************************************
	 *	Utility functions
	 **************************************************************************/

	/**
	 *	Transform a size into a raw number (e.g. from "100px" to "100")
	 *	@param	string	The size
	 *	@return	The number corresponding to the string
	 */
	'size2num' : function (string) {
		return parseInt(string.split('px').join(''));
	},

	/**
	 *	Transform a number into a size in pixels (e.g. from "100" to "100px")
	 *	@param	num	The number
	 *	@return	The string corresponding to the number
	 */	
	'num2size' : function (num) {
		return num+'px';
	},
	
	/**
	 *	Calculate the width of the tooltip according to the length of the
	 *	text that has to be accommodated inside.
	 *	@param	text	The text of the tooltip
	 *	@return	The width of the tooltip
	 */
	'calculateWidth' : function (text) {
		if (text.length>100) {
			return 250;
		} else {
			if (text.length>40) {
				return 200;
			} else {
				return 100;
			}
		}
	},
	
	/**
	 *	Return the position of the cursor
	 *	See: http://www.brainjar.com/dhtml/drag/
	 *	@param	e	An Event object
	 *	@return	An array containing the x and y coordinates of the cursor
	 */
	'getCursorPosition' : function (e) {
		var pos = new Array();
		if (window.scrollX || window.scrollX==0) {
			pos[0] = e.clientX + window.scrollX;
			pos[1] = e.clientY + window.scrollY;
		} else {
			if (window.event.clientX) {
				pos[0] = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
				pos[1] = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
			} else {
				pos = null;
			}
		}
		
		return pos;
	},
	
	/**
	 *	Return the total offset of the element from the left border of the window
	 *	@param	elem	The DOM element
	 *	@return	The total offset from the left border
	 */
	'getTotalOffsetLeft' : function (elem) {
		if (elem.offsetParent) {
			return elem.offsetLeft + this.getTotalOffsetLeft(elem.offsetParent);
		} else {
			return elem.offsetLeft;
		}
	},
	
	/**
	 *	Return the total offset of the element from the top border of the window
	 *	@param	elem	The DOM element
	 *	@return	The total offset from the top border
	 */	
	'getTotalOffsetTop' : function (elem) {
		if (elem.offsetParent) {
			return elem.offsetTop + this.getTotalOffsetTop(elem.offsetParent);
		} else {
			return elem.offsetTop;
		}
	},
	
	/**
	 *	Return the size of the image linked to the Tipmage instance
	 *	@param	e	An Event object
	 *	@return	An array of two elements: width and height of the image
	 */
	'getImageSize' : function (e) {
		var myself = this;
		var size = new Array();
		var image = myself.getEl(myself.imageId);
		
		size[0] = image.width;
		size[1] = image.height;
		
		return size;
	},
	
	/**
	 *	Get an event. Needed for compatibility, since IE has its own way of doing things...
	 *	@param	e	The event (not in IE)
	 *	@return	The event
	 */
	'getEvent' : function (e) {
		if(e) {
			return e;
		} else if (window.event) {
			return window.event;
		}
		return null;	
	},
	
	/**
	 *	Get the target object of an event. Needed for compatibility, since IE uses srcElement
	 *	while the others use target
	 *	See: http://www.quirksmode.org/js/events_properties.html
	 *	@param	e	The event
	 *	@return	The target object
	 */
	'getEventTarget' : function (e) {
		if (e.target) {
			return e.target;
		}
		if (e.srcElement) {
			return e.srcElement;
		}
		return null;
	},
	
	/**
	 *	Replace all newlines in the text with HTML newlines (<br>)
	 *	@param	text	The text with the newlines
	 *	@return	The text with the HTML newlines
	 */
	'nl2br' : function (text) {
		return text.replace(/\n/ig,'<br>');
	},
	
	/**
	 *	Replace all HTML newlines (<br>) in the text with newlines
	 *	@param	text	The text with the HTML newlines
	 *	@return	The text with the newlines
	 */
	'br2nl' : function (text) {
		return text.replace(/<br>/ig,'\n');
	},
	
	/**
	 *	Bind a function to the onLoad event of the page, preserving other
	 *	bindings that may already exist.
	 *	See: http://www.sitepoint.com/blogs/2004/05/26/closures-and-executing-javascript-on-page-load/
	 *	@param	func	The function to bind
	 */
	'addLoadEvent' : function (func) {
		var oldonload = window.onload;
		if (typeof window.onload != 'function') {
			window.onload = func;
		} else {
			window.onload = function() {
				oldonload();
				func();
			}
		}
	},
	
	/**
	 *	Disable selection to avoid blue flickering in IE
	 */
	'disableSelect' : function () {
		document.onselectstart=new Function("return false");
	},
	
	/**
	 *	Enable selection
	 */
	'enableSelect' : function () {
		document.onselectstart=new Function("return true");
	},

	
	/**
	 *	Get the specified object. Based on http://www.quirksmode.org/js/dhtmloptions.html
	 *	Compatible with Mozilla, Explorer 5+, Opera 5+, Konqueror, Safari, iCab, Ice, OmniWeb 4.5,
	 *	Explorer 4+, Opera 6+, iCab, Ice, Omniweb 4.2-
	 *	@param	id	Id of the object to get
	 *	@return The specified object, or null if not found
	 */
	'getEl' : function (id) {
		if (document.getElementById) {
			return document.getElementById(id);
		} else if (document.all) {
			return document.all[id];
		}
		return null;
	},
	
	/**
	 *	Create a new DOM element
	 *	@param	type	The type of the element (i.e. the tag name)
	 *	@param	className	The class name of the element
	 *	@param	id	The id name of the element
	 *	@return	A DOM element
	 */
	'createElement' : function (type,className,id) {
		var elem = document.createElement(type);
		elem.className = className;
		elem.id = id;
		return elem;
	}

};