var x = 25;
var y = 250;
var dx = 1.5;
var dy = -4;
var ctx;
var WIDTH;
var HEIGHT;
var paddlex;
var paddleh = 10;
var paddlew = 75;
var rightDown = false;
var leftDown = false;
var canvasMinX = 0;
var canvasMaxX = 0;
var intervalId = 0;
var bricks;
var NROWS = 5;
var NCOLS = 5;
var BRICKWIDTH;
var BRICKHEIGHT = 15;
var PADDING = 1;

function reset_values() {
	clearInterval(intervalId);
	clear();
	x = 25;
	y = 250;
	dx = 1.5;
	dy = -4;
	paddleh = 10;
	paddlew = 75;
	rightDown = false;
	leftDown = false;
	canvasMinX = 0;
	canvasMaxX = 0;
	intervalId = 0;
	bricks;
	NROWS = 5;
	NCOLS = 5;
	BRICKWIDTH;
	BRICKHEIGHT = 15;
	PADDING = 1;
	init();
	initbricks();
}
	
function init() {
	  ctx = $('#canvas')[0].getContext("2d");
	  WIDTH = $("#canvas").width();
	  HEIGHT = $("#canvas").height();
	  paddlex = WIDTH / 2;
	  BRICKWIDTH = (WIDTH/NCOLS) - 1;
	  canvasMinX = $("#canvas").offset().left;
	  canvasMaxX = canvasMinX + WIDTH;
	  intervalId = setInterval(draw, 10);
	  return intervalId;
}

function circle(x,y,r) {
	ctx.beginPath();
	ctx.arc(x, y, r, 0, Math.PI*2, true);
	ctx.closePath();
	ctx.fill();
}
	
function rect(x,y,w,h) {
	ctx.beginPath();
	ctx.rect(x,y,w,h);
	ctx.closePath();
	ctx.fill();
}
	
function clear() {
	ctx.clearRect(0, 0, WIDTH, HEIGHT);
	rect(0, 0, WIDTH,HEIGHT);
}
	
function onKeyDown(evt) {
	if (evt.keyCode == 39) rightDown = true;
	else if (evt.keyCode == 37) leftDown = true;
}
	
function onKeyUp(evt) {
	if (evt.keyCode == 39) rightDown = false;
	else if (evt.keyCode == 37) leftDown = false;
}
	
	$(document).keydown(onKeyDown);
	$(document).keyup(onKeyUp);
	
	function onMouseMove(evt) {
	  if (evt.pageX > canvasMinX && evt.pageX < canvasMaxX) {
		paddlex = Math.max(evt.pageX - canvasMinX - (paddlew/2), 0);
		paddlex = Math.min(WIDTH - paddlew, paddlex);
	  }
	}
	
	$(document).mousemove(onMouseMove);
	
	function initbricks() {
		bricks = new Array(NROWS);
		for (i=0; i < NROWS; i++) {
			bricks[i] = new Array(NCOLS);
			for (j=0; j < NCOLS; j++) {
				bricks[i][j] = 1;
			}
		}
	}
	
	function drawbricks() {
	  for (i=0; i < NROWS; i++) {
		ctx.fillStyle = rowcolors[i];
		for (j=0; j < NCOLS; j++) {
		  if (bricks[i][j] == 1) {
			rect((j * (BRICKWIDTH + PADDING)) + PADDING,
				 (i * (BRICKHEIGHT + PADDING)) + PADDING,
				 BRICKWIDTH, BRICKHEIGHT);
		  }
		}
	  }
	}
	
	var ballr = 10;
	var rowcolors = ["#FFFFFF", "#FFFFFFFFF", "#FFFFFF", "#FFFFFF", "#FFFFFF"];
	var paddlecolor = "#FFFFFF";
	var ballcolor = "#FFFFFF";
	var backcolor = "#000000";
	
	function draw() {
	  ctx.fillStyle = backcolor;
	  clear();
	  ctx.fillStyle = ballcolor;
	  circle(x, y, ballr);
	
	  if (rightDown) paddlex += 5;
	  else if (leftDown) paddlex -= 5;
	  ctx.fillStyle = paddlecolor;
	  rect(paddlex, HEIGHT-paddleh, paddlew, paddleh);
	
	  drawbricks();
	
	  rowheight = BRICKHEIGHT + PADDING;
	  colwidth = BRICKWIDTH + PADDING;
	  row = Math.floor(y/rowheight);
	  col = Math.floor(x/colwidth);
	  //reverse the ball and mark the brick as broken
	  if (y < NROWS * rowheight && row >= 0 && col >= 0 && bricks[row][col] == 1) {
		dy = -dy;
		bricks[row][col] = 0;
	  }
	
	  if (x + dx + ballr > WIDTH || x + dx - ballr < 0)
		dx = -dx;
	
	  if (y + dy - ballr < 0)
		dy = -dy;
	  else if (y + dy + ballr > HEIGHT - paddleh) {
		if (x > paddlex && x < paddlex + paddlew) {
		  //move the ball differently based on where it hit the paddle
		  dx = 8 * ((x-(paddlex+paddlew/2))/paddlew);
		  dy = -dy;
		}
		else if (y + dy + ballr > HEIGHT)
		  clearInterval(intervalId);
	  }
	
	  x += dx;
	  y += dy;
	}