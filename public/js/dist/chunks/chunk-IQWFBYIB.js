import{a as r,b as l,d as T}from"./chunk-NIXCKU33.js";var P=r.toast?.duration??2400,p=null,x={success:"✓",error:"✕",info:"✦"},v={success:"toast-success",error:"toast-error",info:"toast-info"};function U(t){return String(t??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function b(){return T("#toast")}function E(t){return x[t]||x.success}function S(t){return v[t]||v.success}function y(t){t.classList.remove("toast-success","toast-error","toast-info","show")}function $(t,e,n){t.innerHTML=`
        <span class="toast-wing toast-wing-left"></span>

        <div class="toast-content">

            <span class="toast-icon">
                ${E(n)}
            </span>

            <span class="toast-message">
                ${U(e)}
            </span>

        </div>

        <span class="toast-wing toast-wing-right"></span>

        <span class="toast-shine"></span>
    `}function z(t){t.offsetWidth}function C(){p&&(clearTimeout(p),p=null)}function F(t="Sauvegardé",e="success"){let n=b();if(!n){l("TOAST","#toast introuvable");return}l("TOAST",e,t),C(),y(n),n.classList.add(S(e)),$(n,t,e),z(n),n.classList.add("show"),p=window.setTimeout(()=>{n.classList.remove("show")},P)}function W(){let t=window.flashToast;delete window.flashToast,t?.message&&F(t.message,t.type??"success")}function q(t=""){return(r.baseUri+t).replace(/\/{2,}/g,"/")}function N(t=window.location.pathname){let e=r.baseUri==="/"?"":r.baseUri.replace(/\/$/,"");return e!==""&&t===e?"/":e!==""&&t.startsWith(`${e}/`)?t.slice(e.length):t}function H(t){let e=new URL(t,window.location.origin),n=e.pathname.replace(/\/+/g,"/");return n===""&&(n="/"),e.pathname=n,e.toString()}function a(t){let e=new URL(H(t));return e.hash="",e.toString()}function G(t){if(!(t instanceof HTMLAnchorElement)||!t.href)return!0;let e=new URL(t.href,window.location.origin);return!!(e.origin!==window.location.origin||t.target==="_blank"||t.hasAttribute("download")||t.dataset.noRouter!==void 0||e.hash&&a(e.href)===a(location.href)||/\.(jpg|jpeg|png|gif|webp|svg|pdf|zip|mp4|webm)$/i.test(e.pathname))}var u=new Map;function d(t){return new URL(a(t)).pathname.replace(/\/+$/,"")||"/"}function R(t,e,n){return t===e||n&&t.startsWith(e==="/"?"/":`${e}/`)}function V(t,{descendants:e=!0}={}){u.set(d(t),e||u.get(d(t))===!0)}function X(t){let e=d(t);for(let[n,i]of u)if(R(e,n,i))return!0;return!1}function Y(t){let e=d(t);for(let[n,i]of u)R(e,n,i)&&u.delete(n)}var g=window.__PREFETCH_STATE__||={initialized:!1,cache:new Map,inFlight:new Map,invalidated:new Set},o=g.cache,f=g.inFlight,s=g.invalidated;function _(t){return Date.now()-t.timestamp>r.prefetch.cacheDuration}function I(){for(;o.size>r.prefetch.cacheLimit;){let t=o.keys().next().value;if(!t)return;o.delete(t)}}function at(t){let e=a(t);if(s.has(e))return null;let n=o.get(e);return n?_(n)?(o.delete(e),null):(o.delete(e),o.set(e,n),{type:"page",page:n.page}):null}function st(t,e){let n=a(t);o.delete(n),o.set(n,{page:e.page,timestamp:Date.now()}),s.delete(n),I()}function it(t,{descendants:e=!0}={}){let n=a(t),i=new URL(n),h=i.pathname.replace(/\/+$/,"")||"/",L=new Set([n,...o.keys(),...f.keys()]);for(let c of L){let m=new URL(c),w=m.pathname.replace(/\/+$/,"")||"/";if(m.origin!==i.origin||w!==h&&(!e||!w.startsWith(h==="/"?"/":`${h}/`)))continue;s.delete(c),s.add(c),o.delete(c),f.get(c)?.controller.abort(),f.delete(c)}let A=Math.max(1,r.prefetch.cacheLimit*4);for(;s.size>A;)s.delete(s.values().next().value);l("PREFETCH","invalidate",n)}function ct(t){let e=a(t);return s.has(e)?null:f.get(e)?.promise??null}export{F as a,W as b,q as c,N as d,H as e,a as f,G as g,g as h,f as i,s as j,at as k,st as l,it as m,ct as n,V as o,X as p,Y as q};
