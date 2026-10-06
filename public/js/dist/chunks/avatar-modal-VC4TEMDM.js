import{a as n}from"./chunk-LSNWB56N.js";import"./chunk-MOUFMENJ.js";import{c as r}from"./chunk-GZNDPYT5.js";import"./chunk-H2PN2WI6.js";import"./chunk-JYGFMSCX.js";function m(o){return new Promise(d=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir un avatar
                    </h3>

                    <div class="media-picker-grid media-picker-grid--avatars avatar-modal-grid">

                        ${o.map(a=>`
                                <button
                                    class="media-picker-item media-picker-item--avatar avatar-modal-item"
                                    data-avatar="${a.avatar}"
                                    type="button"
                                    ${a.unlocked?"":"disabled"}
                                    aria-label="${a.avatar} — ${a.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${r(`images/profil/avatar/thumbnail/${a.avatar}.${a.avatar_extension}`)}"
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
            `;let t=n(e,d);e.addEventListener("click",a=>{let i=a.target.closest(".avatar-modal-item");i&&e.contains(i)&&!i.disabled&&t(i.dataset.avatar)}),e.addEventListener("click",a=>{a.target===e&&t()})})}export{m as avatarModal};
