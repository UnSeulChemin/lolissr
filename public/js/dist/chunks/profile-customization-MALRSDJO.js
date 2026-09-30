import{a as m,b as v}from"./chunk-JBRXXNAP.js";import"./chunk-BF3LETK2.js";import"./chunk-HXYIU4QO.js";import{a as u}from"./chunk-G6XN5UE2.js";import{a as p}from"./chunk-TE3VHEBK.js";import{a as s,c as i}from"./chunk-ME5BFOWY.js";import{c,d}from"./chunk-VUXMF5ZT.js";import"./chunk-NIXCKU33.js";function b(t){return new Promise(n=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir un avatar
                    </h3>

                    <div class="media-picker-grid media-picker-grid--avatars avatar-modal-grid">

                        ${t.map(a=>`
                                <button
                                    class="media-picker-item media-picker-item--avatar avatar-modal-item"
                                    data-avatar="${a.avatar}"
                                    type="button"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${i(`images/profil/avatar/thumbnail/${a.avatar}.${a.avatar_extension}`)}"
                                        alt="${a.avatar}"
                                        draggable="false"
                                    >

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let r=m(e,n);e.querySelectorAll(".avatar-modal-item").forEach(a=>{a.addEventListener("click",()=>r(a.dataset.avatar))}),e.addEventListener("click",a=>{a.target===e&&r()})})}function g(t){return new Promise(n=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir une bannière
                    </h3>

                    <div class="media-picker-grid banner-modal-grid">

                        ${t.map(a=>`
                                <button
                                    class="media-picker-item media-picker-item--banner banner-modal-item"
                                    data-banner="${a.banner}"
                                    type="button"
                                    ${a.unlocked?"":"disabled"}
                                    aria-label="${a.banner} — ${a.unlocked?"Disponible":"Verrouillée"}, ${a.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${i(`images/profil/banner/thumbnail/${a.banner}.${a.banner_extension}?v=20260929-sakura-v2`)}"
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
            `;let r=m(e,n);e.querySelectorAll(".banner-modal-item:not(:disabled)").forEach(a=>{a.addEventListener("click",()=>r(a.dataset.banner))}),e.addEventListener("click",a=>{a.target===e&&r()})})}function y(t,n){return new Promise(e=>{let r=document.createElement("div");r.className="confirm-modal-overlay frame-modal-overlay",r.innerHTML=`
                <div class="confirm-modal frame-modal">

                    <h3>
                        Choisir un cadre
                    </h3>

                    <div class="title-modal-list frame-modal-grid">

                        ${t.map(o=>`
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
                                            src="${n}"
                                            alt=""
                                            draggable="false"
                                        >

                                        <img loading="lazy" decoding="async"
                                            class="profile-frame"
                                            src="${i(`images/profil/frame/thumbnail/${o.frame}.${o.frame_extension}`)}"
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
            `;let a=m(r,e);r.querySelectorAll(".frame-modal-item:not(:disabled)").forEach(o=>{o.addEventListener("click",()=>a(o.dataset.frame))}),r.addEventListener("click",o=>{o.target===r&&a()})})}function f(){p(i("profil"))}async function h(t){let n=await c(i("profil/ajax/titles"),{signal:t});if(t.aborted)return;let e=await v(n.data.titles);if(!e)return;let r=await d(i("profil/ajax/update-title"),{title:e});if(f(),t.aborted)return;document.querySelectorAll(".profile-customization-title, .profile-subtitle").forEach(l=>{l.dataset.titleStyle=r.data.style});let a=document.querySelector(".profile-customization-title");a&&(a.textContent=e);let o=document.querySelector(".profile-subtitle");o&&(o.textContent=e),s("Titre mis à jour","success")}async function k(t){let n=await c(i("profil/ajax/avatars"),{signal:t});if(t.aborted)return;let e=await b(n.data.avatars);if(!e)return;let r=await d(i("profil/ajax/update-avatar"),{avatar:e});if(f(),t.aborted)return;let a=i(`images/profil/avatar/thumbnail/${r.data.avatar}.${r.data.avatar_extension}`),o=document.querySelector(".profile-customization-avatar img");document.querySelectorAll(".site-profile-avatar").forEach($=>{$.src=a}),o&&(o.src=a);let l=document.querySelector(".profile-avatar img");l&&(l.src=a),s("Avatar mis à jour","success")}async function x(t){let n=await c(i("profil/ajax/banners"),{signal:t});if(t.aborted)return;let e=await g(n.data.banners);if(!e)return;let r=await d(i("profil/ajax/update-banner"),{banner:e});if(f(),t.aborted)return;let a=i(`images/profil/banner/thumbnail/${r.data.banner}.${r.data.banner_extension}?v=20260929-sakura-v2`);document.querySelectorAll(".profile-customization-banner img, .profile-banner img").forEach(o=>{o.src=a}),s("Bannière mise à jour","success")}async function j(t){let n=await c(i("profil/ajax/frames"),{signal:t});if(t.aborted)return;let e=document.querySelector(".profile-avatar-image"),r=await y(n.data.frames,e?.src??"");if(!r)return;let a=await d(i("profil/ajax/update-frame"),{frame:r});if(f(),t.aborted)return;let o=i(`images/profil/frame/thumbnail/${a.data.frame}.${a.data.frame_extension}`);document.querySelectorAll(".profile-customization-avatar .profile-frame, .profile-avatar .profile-frame, .site-profile-frame").forEach(l=>{l.src=o}),s("Cadre mis à jour","success")}function G(){let t=new AbortController,n=!1;u(()=>t.abort());for(let[e,r]of[[".js-profile-title",h],[".js-profile-avatar",k],[".js-profile-banner",x],[".js-profile-frame",j]])document.querySelector(e)?.addEventListener("click",async()=>{if(!n){n=!0;try{await r(t.signal)}catch(a){!t.signal.aborted&&a?.name!=="AbortError"&&s("Impossible de modifier le profil. Réessaie.","error")}finally{n=!1}}},{signal:t.signal})}export{G as initProfileCustomization};
