<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="preload" href="/cms/css/quill.css" as="style" onload="this.rel='stylesheet'" onerror="this.rel='stylesheet'">
    {{-- <link rel="preload" href="/cms/css/panel.css" as="style" onload="this.rel='stylesheet'"> --}}

    <link rel="preload" type="text/css" href="/cms/css/panel.css" as="style" onload="this.rel='stylesheet'" onerror="this.rel='stylesheet'">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome-font-awesome.min.css">

    <link href="/fonts/PTSans.ttc" as="font" type="font/woff2" crossorigin>

    <noscript>
        <link rel="stylesheet" href="/css/quill.css">
        <link rel=stylesheet href="/css/panel.css">
    </noscript>

    <script>
        (function(w){"use strict";if(!w.loadCSS){w.loadCSS=function(){}}var rp=loadCSS.relpreload={};rp.support=(function(){var ret;try{ret=w.document.createElement("link").relList.supports("preload")}catch(e){ret=false}return function(){return ret}})();rp.bindMediaToggle=function(link){var finalMedia=link.media||"all";function enableStylesheet(){if(link.addEventListener){link.removeEventListener("load",enableStylesheet)}else if(link.attachEvent){link.detachEvent("onload",enableStylesheet)}link.setAttribute("onload",null);link.media=finalMedia}if(link.addEventListener){link.addEventListener("load",enableStylesheet)}else if(link.attachEvent){link.attachEvent("onload",enableStylesheet)}setTimeout(function(){link.rel="stylesheet";link.media="only x"});setTimeout(enableStylesheet,3000)};rp.poly=function(){if(rp.support()){return}var links=w.document.getElementsByTagName("link");for(var i=0;i<links.length;i+=1){var link=links[i];if(link.rel==="preload"&&link.getAttribute("as")==="style"&&!link.getAttribute("data-loadcss")){link.setAttribute("data-loadcss",true);rp.bindMediaToggle(link)}}};if(!rp.support()){rp.poly();var run=w.setInterval(rp.poly,500);if(w.addEventListener){w.addEventListener("load",function(){rp.poly();w.clearInterval(run)})}else if(w.attachEvent){w.attachEvent("onload",function(){rp.poly();w.clearInterval(run)})}}if(typeof exports!=="undefined"){exports.loadCSS=loadCSS}else{w.loadCSS=loadCSS}}(typeof global!=="undefined"?global:this));
    </script>
</head>
<body>
<div id="app">

</div>

<script src="/cms/js/admin.js" defer></script>
</body>
</html>
