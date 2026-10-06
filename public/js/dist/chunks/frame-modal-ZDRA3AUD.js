import{a as t}from"./chunk-LSNWB56N.js";import"./chunk-MOUFMENJ.js";import{c as o}from"./chunk-GZNDPYT5.js";import"./chunk-H2PN2WI6.js";import"./chunk-JYGFMSCX.js";function c(r,n){return new Promise(s=>{let e=document.createElement("div");e.className="confirm-modal-overlay frame-modal-overlay",e.innerHTML=`
                <div class="confirm-modal frame-modal">

                    <h3>
                        Choisir un cadre
                    </h3>

                    <div class="title-modal-list frame-modal-grid">

                        ${r.map(a=>`
                                <button
                                    class="title-modal-item frame-modal-item"
                                    data-frame="${a.frame}"
                                    type="button"
                                    ${a.unlocked?"":"disabled"}
                                    aria-label="${a.frame} — ${a.unlocked?"Disponible":"Verrouillé"}, ${a.requirement}"
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
                                            src="${o(`images/profil/frame/thumbnail/${a.frame}.${a.frame_extension}`)}"
                                            alt="${a.frame}"
                                            draggable="false"
                                        >

                                    </div>

                                    <span class="frame-modal-status">
                                        ${a.unlocked?"":'<span aria-hidden="true">🔒</span> '}${a.requirement}
                                    </span>

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let l=t(e,s);e.addEventListener("click",a=>{let i=a.target.closest(".frame-modal-item");i&&e.contains(i)&&!i.disabled&&l(i.dataset.frame)}),e.addEventListener("click",a=>{a.target===e&&l()})})}export{c as frameModal};
