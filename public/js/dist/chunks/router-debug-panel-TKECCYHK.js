import{a as t,b as o}from"./chunk-UIBCKW3Z.js";var d="router-debug-panel",a=!1;function u(){return t.debug}function c(){let e=document.createElement("div");return e.id=d,e.innerHTML=`
        <div class="router-debug-title">
            DÉBOGAGE SPA
        </div>

        <div class="router-debug-content">
        </div>
    `,document.body.appendChild(e),e}function l(){return document.getElementById(d)||c()}function g(e){if(!u())return;let n=l().querySelector(".router-debug-content");if(!n)return;let r=document.createElement("div");for(r.textContent=`[${new Date().toLocaleTimeString()}] ${e}`,n.prepend(r);n.children.length>t.debugPanel.maxLogs;)n.lastChild?.remove()}function s(){a||(a=!0,u()&&(["navigation:start","navigation:fetch","navigation:render","navigation:ready","navigation:error","navigation:abort"].forEach(e=>{document.addEventListener(e,i=>{g(`${e} → ${i.detail?.to||""}`)})}),o("DEBUG_PANEL","initialized")))}export{s as initRouterDebugPanel};
