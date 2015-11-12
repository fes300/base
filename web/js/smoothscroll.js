// SmoothScroll for websites v1.2.1
// Licensed under the terms of the MIT license.
 
// People involved
//  - Balazs Galambosi (maintainer)  
//  - Michael Herf     (Pulse Algorithm)
 
(function(){
  
// Scroll Variables (tweakable)
var defaultOptions = {
 
    // Scrolling Core
    frameRate        : 150, // [Hz]
    animationTime    : 600, // [px]
    stepSize         : 120, // [px]
 
    // Pulse (less tweakable)
    // ratio of "tail" to "acceleration"
    pulseAlgorithm   : true,
    pulseScale       : 8,
    pulseNormalize   : 1,
 
    // Acceleration
    accelerationDelta : 20,  // 20
    accelerationMax   : 1,   // 1
 
    // Keyboard Settings
    keyboardSupport   : true,  // option
    arrowScroll       : 50,     // [px]
 
    // Other
    touchpadSupport   : true,
    fixedBackground   : true, 
    excluded          : ""    
};
 
var options = defaultOptions;
 
function initTest(){var a=!1;a&&removeEvent("keydown",keydown),options.keyboardSupport&&!a&&addEvent("keydown",keydown)}function init(){if(document.body){var a=document.body,b=document.documentElement,c=window.innerHeight,d=a.scrollHeight;if(root=document.compatMode.indexOf("CSS")>=0?b:a,activeElement=a,initTest(),initDone=!0,top!=self)isFrame=!0;else if(d>c&&(a.offsetHeight<=c||b.offsetHeight<=c)){var e=!1,f=function(){e||b.scrollHeight==document.height||(e=!0,setTimeout(function(){b.style.height=document.height+"px",e=!1},500))};if(b.style.height="auto",setTimeout(f,10),root.offsetHeight<=c){var g=document.createElement("div");g.style.clear="both",a.appendChild(g)}}options.fixedBackground||isExcluded||(a.style.backgroundAttachment="scroll",b.style.backgroundAttachment="scroll")}}function scrollArray(a,b,c,d){if(d||(d=1e3),directionCheck(b,c),1!=options.accelerationMax){var e=+new Date,f=e-lastScroll;if(f<options.accelerationDelta){var g=(1+30/f)/2;g>1&&(g=Math.min(g,options.accelerationMax),b*=g,c*=g)}lastScroll=+new Date}if(que.push({x:b,y:c,lastX:0>b?.99:-.99,lastY:0>c?.99:-.99,start:+new Date}),!pending){var h=a===document.body,i=function(e){for(var f=+new Date,g=0,j=0,k=0;k<que.length;k++){var l=que[k],m=f-l.start,n=m>=options.animationTime,o=n?1:m/options.animationTime;options.pulseAlgorithm&&(o=pulse(o));var p=l.x*o-l.lastX>>0,q=l.y*o-l.lastY>>0;g+=p,j+=q,l.lastX+=p,l.lastY+=q,n&&(que.splice(k,1),k--)}h?window.scrollBy(g,j):(g&&(a.scrollLeft+=g),j&&(a.scrollTop+=j)),b||c||(que=[]),que.length?requestFrame(i,a,d/options.frameRate+1):pending=!1};requestFrame(i,a,0),pending=!0}}function wheel(a){initDone||init();var b=a.target,c=overflowingAncestor(b);if(!c||a.defaultPrevented||isNodeName(activeElement,"embed")||isNodeName(b,"embed")&&/\.pdf/i.test(b.src))return!0;var d=a.wheelDeltaX||0,e=a.wheelDeltaY||0;return d||e||(e=a.wheelDelta||0),!options.touchpadSupport&&isTouchpad(e)?!0:(Math.abs(d)>1.2&&(d*=options.stepSize/120),Math.abs(e)>1.2&&(e*=options.stepSize/120),scrollArray(c,-d,-e),void a.preventDefault())}function keydown(a){var b=a.target,c=a.ctrlKey||a.altKey||a.metaKey||a.shiftKey&&a.keyCode!==key.spacebar;if(/input|textarea|select|embed/i.test(b.nodeName)||b.isContentEditable||a.defaultPrevented||c)return!0;if(isNodeName(b,"button")&&a.keyCode===key.spacebar)return!0;var d,e=0,f=0,g=overflowingAncestor(activeElement),h=g.clientHeight;switch(g==document.body&&(h=window.innerHeight),a.keyCode){case key.up:f=-options.arrowScroll;break;case key.down:f=options.arrowScroll;break;case key.spacebar:d=a.shiftKey?1:-1,f=-d*h*.9;break;case key.pageup:f=.9*-h;break;case key.pagedown:f=.9*h;break;case key.home:f=-g.scrollTop;break;case key.end:var i=g.scrollHeight-g.scrollTop-h;f=i>0?i+10:0;break;case key.left:e=-options.arrowScroll;break;case key.right:e=options.arrowScroll;break;default:return!0}scrollArray(g,e,f),a.preventDefault()}function mousedown(a){activeElement=a.target}function setCache(a,b){for(var c=a.length;c--;)cache[uniqueID(a[c])]=b;return b}function overflowingAncestor(a){var b=[],c=root.scrollHeight;do{var d=cache[uniqueID(a)];if(d)return setCache(b,d);if(b.push(a),c===a.scrollHeight){if(!isFrame||root.clientHeight+10<c)return setCache(b,document.body)}else if(a.clientHeight+10<a.scrollHeight&&(overflow=getComputedStyle(a,"").getPropertyValue("overflow-y"),"scroll"===overflow||"auto"===overflow))return setCache(b,a)}while(a=a.parentNode)}function addEvent(a,b,c){window.addEventListener(a,b,c||!1)}function removeEvent(a,b,c){window.removeEventListener(a,b,c||!1)}function isNodeName(a,b){return(a.nodeName||"").toLowerCase()===b.toLowerCase()}function directionCheck(a,b){a=a>0?1:-1,b=b>0?1:-1,(direction.x!==a||direction.y!==b)&&(direction.x=a,direction.y=b,que=[],lastScroll=0)}function isTouchpad(a){if(a){a=Math.abs(a),deltaBuffer.push(a),deltaBuffer.shift(),clearTimeout(deltaBufferTimer);var b=isDivisible(deltaBuffer[0],120)&&isDivisible(deltaBuffer[1],120)&&isDivisible(deltaBuffer[2],120);return!b}}function isDivisible(a,b){return Math.floor(a/b)==a/b}function pulse_(a){var b,c,d;return a*=options.pulseScale,1>a?b=a-(1-Math.exp(-a)):(c=Math.exp(-1),a-=1,d=1-Math.exp(-a),b=c+d*(1-c)),b*options.pulseNormalize}function pulse(a){return a>=1?1:0>=a?0:(1==options.pulseNormalize&&(options.pulseNormalize/=pulse_(1)),pulse_(a))}var isExcluded=!1,isFrame=!1,direction={x:0,y:0},initDone=!1,root=document.documentElement,activeElement,observer,deltaBuffer=[120,120,120],key={left:37,up:38,right:39,down:40,spacebar:32,pageup:33,pagedown:34,end:35,home:36},options=defaultOptions,que=[],pending=!1,lastScroll=+new Date,cache={};setInterval(function(){cache={}},1e4);var uniqueID=function(){var a=0;return function(b){return b.uniqueID||(b.uniqueID=a++)}}(),deltaBufferTimer,requestFrame=function(){return window.requestAnimationFrame||window.webkitRequestAnimationFrame||function(a,b,c){window.setTimeout(a,c||1e3/60)}}(),isChrome=/chrome/i.test(window.navigator.userAgent),wheelEvent=null;"onwheel"in document.createElement("div")?wheelEvent="wheel":"onmousewheel"in document.createElement("div")&&(wheelEvent="mousewheel"),wheelEvent&&isChrome&&(addEvent(wheelEvent,wheel),addEvent("mousedown",mousedown),addEvent("load",init));

})();