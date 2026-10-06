import{a as m,b}from"./chunk-QXFNI622.js";import"./chunk-BF3LETK2.js";import"./chunk-HXYIU4QO.js";import{a as p}from"./chunk-MOUFMENJ.js";import{a as v}from"./chunk-NW274NC3.js";import{a as n,c as d}from"./chunk-HMCMA5OB.js";import{c,d as f}from"./chunk-34EGRRXG.js";import{a as s}from"./chunk-OITH5TX2.js";import"./chunk-BFFYOOEW.js";import"./chunk-H2PN2WI6.js";import"./chunk-JYGFMSCX.js";function g(r){return new Promise(i=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir un avatar
                    </h3>

                    <div class="media-picker-grid media-picker-grid--avatars avatar-modal-grid">

                        ${r.map(a=>`
                                <button
                                    class="media-picker-item media-picker-item--avatar avatar-modal-item"
                                    data-avatar="${a.avatar}"
                                    type="button"
                                    ${a.unlocked?"":"disabled"}
                                    aria-label="${a.avatar} — ${a.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${d(`images/profil/avatar/thumbnail/${a.avatar}.${a.avatar_extension}`)}"
                                        alt="${a.avatar}"
                                        draggable="false"
                                    >

                                    <span class="banner-modal-status">
                                        ${a.unlocked?"":'<span aria-hidden="true">🔒</span> '}${a.requirement}
                                    </span>

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let t=m(e,i);e.querySelectorAll(".avatar-modal-item:not(:disabled)").forEach(a=>{a.addEventListener("click",()=>t(a.dataset.avatar))}),e.addEventListener("click",a=>{a.target===e&&t()})})}function $(r){return new Promise(i=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir une bannière
                    </h3>

                    <div class="media-picker-grid banner-modal-grid">

                        ${r.map(a=>`
                                <button
                                    class="media-picker-item media-picker-item--banner banner-modal-item"
                                    data-banner="${a.banner}"
                                    type="button"
                                    ${a.unlocked?"":"disabled"}
                                    aria-label="${a.banner} — ${a.unlocked?"Disponible":"Verrouillée"}, ${a.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${d(`images/profil/banner/thumbnail/${a.banner}.${a.banner_extension}`)}"
                                        alt="${a.banner}"
                                        draggable="false"
                                    >
                                    <span class="banner-modal-status">
                                        ${a.unlocked?"":'<span aria-hidden="true">🔒</span> '}${a.requirement}
                                    </span>

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let t=m(e,i);e.querySelectorAll(".banner-modal-item:not(:disabled)").forEach(a=>{a.addEventListener("click",()=>t(a.dataset.banner))}),e.addEventListener("click",a=>{a.target===e&&t()})})}function y(r,i){return new Promise(e=>{let t=document.createElement("div");t.className="confirm-modal-overlay frame-modal-overlay",t.innerHTML=`
                <div class="confirm-modal frame-modal">

                    <h3>
                        Choisir un cadre
                    </h3>

                    <div class="title-modal-list frame-modal-grid">

                        ${r.map(o=>`
                                <button
                                    class="title-modal-item frame-modal-item"
                                    data-frame="${o.frame}"
                                    type="button"
                                    ${o.unlocked?"":"disabled"}
                                    aria-label="${o.frame} — ${o.unlocked?"Disponible":"Verrouillé"}, ${o.requirement}"
                                >

                                    <div class="profile-customization-avatar">

                                        <img loading="lazy" decoding="async"
                                            class="profile-avatar-image"
                                            src="${i}"
                                            alt=""
                                            draggable="false"
                                        >

                                        <img loading="lazy" decoding="async"
                                            class="profile-frame"
                                            src="${d(`images/profil/frame/thumbnail/${o.frame}.${o.frame_extension}`)}"
                                            alt="${o.frame}"
                                            draggable="false"
                                        >

                                    </div>

                                    <span class="frame-modal-status">
                                        ${o.unlocked?"":'<span aria-hidden="true">🔒</span> '}${o.requirement}
                                    </span>

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let a=m(t,e);t.querySelectorAll(".frame-modal-item:not(:disabled)").forEach(o=>{o.addEventListener("click",()=>a(o.dataset.frame))}),t.addEventListener("click",o=>{o.target===t&&a()})})}function u(){v(n("profil"))}async function k(r){let i=await c(n("profil/ajax/titles"),{signal:r});if(r.aborted)return;let e=await b(i.data.titles);if(!e)return;let t=await f(n("profil/ajax/update-title"),{title:e});if(u(),r.aborted)return;document.querySelectorAll(".profile-customization-title, .profile-subtitle").forEach(l=>{l.dataset.titleStyle=t.data.style});let a=document.querySelector(".profile-customization-title");a&&(a.textContent=e);let o=document.querySelector(".profile-subtitle");o&&(o.textContent=e),s("Titre mis à jour","success")}async function x(r){let i=await c(n("profil/ajax/avatars"),{signal:r});if(r.aborted)return;let e=await g(i.data.avatars);if(!e)return;let t=await f(n("profil/ajax/update-avatar"),{avatar:e});if(u(),r.aborted)return;let a=n(`images/profil/avatar/thumbnail/${t.data.avatar}.${t.data.avatar_extension}`),o=document.querySelector(".profile-customization-avatar img");document.querySelectorAll(".site-profile-avatar").forEach(h=>{h.src=a}),o&&(o.src=a);let l=document.querySelector(".profile-avatar img");l&&(l.src=a),s("Avatar mis à jour","success")}async function j(r){let i=await c(n("profil/ajax/banners"),{signal:r});if(r.aborted)return;let e=await $(i.data.banners);if(!e)return;let t=await f(n("profil/ajax/update-banner"),{banner:e});if(u(),r.aborted)return;let a=n(`images/profil/banner/thumbnail/${t.data.banner}.${t.data.banner_extension}?v=20260929-sakura-v2`);document.querySelectorAll(".profile-customization-banner img, .profile-banner img").forEach(o=>{o.src=a}),s("Bannière mise à jour","success")}async function q(r){let i=await c(n("profil/ajax/frames"),{signal:r});if(r.aborted)return;let e=document.querySelector(".profile-avatar-image"),t=await y(i.data.frames,e?.src??"");if(!t)return;let a=await f(n("profil/ajax/update-frame"),{frame:t});if(u(),r.aborted)return;let o=n(`images/profil/frame/thumbnail/${a.data.frame}.${a.data.frame_extension}`);document.querySelectorAll(".profile-customization-avatar .profile-frame, .profile-avatar .profile-frame, .site-profile-frame").forEach(l=>{l.src=o}),s("Cadre mis à jour","success")}function J(){let r=new AbortController,i=!1;p(()=>r.abort());for(let[e,t]of[[".js-profile-title",k],[".js-profile-avatar",x],[".js-profile-banner",j],[".js-profile-frame",q]])document.querySelector(e)?.addEventListener("click",async()=>{if(!i){i=!0;try{await t(r.signal)}catch(a){!r.signal.aborted&&a?.name!=="AbortError"&&s("Impossible de modifier le profil. Réessaie.","error")}finally{i=!1}}},{signal:r.signal})}export{J as initProfileCustomization};
