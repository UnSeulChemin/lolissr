import{a as c}from"./chunk-BFFYOOEW.js";import{a as e}from"./chunk-H2PN2WI6.js";import{a as n}from"./chunk-JYGFMSCX.js";var l=n.toast?.duration??2400,a=null,i={success:"✓",error:"✕",info:"✦"},r={success:"toast-success",error:"toast-error",info:"toast-info"};function u(s){return String(s??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function f(){return c("#toast")}function T(s){return i[s]||i.success}function p(s){return r[s]||r.success}function m(s){s.classList.remove("toast-success","toast-error","toast-info","show")}function d(s,o,t){s.innerHTML=`
        <span class="toast-wing toast-wing-left"></span>

        <div class="toast-content">

            <span class="toast-icon">
                ${T(t)}
            </span>

            <span class="toast-message">
                ${u(o)}
            </span>

        </div>

        <span class="toast-wing toast-wing-right"></span>

        <span class="toast-shine"></span>
    `}function g(s){s.offsetWidth}function w(){a&&(clearTimeout(a),a=null)}function h(s="Sauvegardé",o="success"){let t=f();if(!t){e("TOAST","#toast introuvable");return}e("TOAST",o,s),w(),m(t),t.classList.add(p(o)),d(t,s,o),g(t),t.classList.add("show"),a=window.setTimeout(()=>{t.classList.remove("show")},l)}function L(){let s=window.flashToast;delete window.flashToast,s?.message&&h(s.message,s.type??"success")}export{h as a,L as b};
