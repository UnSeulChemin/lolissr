import{a as r}from"./chunk-SJEDEYSR.js";import"./chunk-MOUFMENJ.js";import{c as t}from"./chunk-GZNDPYT5.js";import"./chunk-H2PN2WI6.js";import"./chunk-JYGFMSCX.js";function m(o){return new Promise(l=>{let a=document.createElement("div");a.className="confirm-modal-overlay",a.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        Choisir une bannière
                    </h3>

                    <div class="media-picker-grid banner-modal-grid">

                        ${o.map(e=>`
                                <button
                                    class="media-picker-item media-picker-item--banner banner-modal-item"
                                    data-banner="${e.banner}"
                                    type="button"
                                    ${e.unlocked?"":"disabled"}
                                    aria-label="${e.banner} — ${e.unlocked?"Disponible":"Verrouillée"}, ${e.requirement}"
                                >

                                    <img loading="lazy" decoding="async"
                                        src="${t(`images/profil/banner/thumbnail/${e.banner}.${e.banner_extension}`)}"
                                        alt="${e.banner}"
                                        draggable="false"
                                    >
                                    <span class="banner-modal-status">
                                        ${e.unlocked?"":'<span aria-hidden="true">🔒</span> '}${e.requirement}
                                    </span>

                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let i=r(a,l);a.addEventListener("click",e=>{let n=e.target.closest(".banner-modal-item");n&&a.contains(n)&&!n.disabled&&i(n.dataset.banner)}),a.addEventListener("click",e=>{e.target===a&&i()})})}export{m as bannerModal};
