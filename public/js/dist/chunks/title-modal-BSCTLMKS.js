import{a as i}from"./chunk-LSNWB56N.js";import"./chunk-MOUFMENJ.js";import"./chunk-H2PN2WI6.js";import"./chunk-JYGFMSCX.js";function d(o){return new Promise(n=>{let t=document.createElement("div");t.className="confirm-modal-overlay title-modal-overlay",t.innerHTML=`
                <div class="confirm-modal title-modal" role="dialog" aria-modal="true" aria-label="Choisir un titre">

                    <h3>
                        Choisir un titre
                    </h3>

                    <div class="title-modal-list">

                        ${o.map(e=>`
                                <button
                                    class="title-modal-item"
                                    data-title="${e.title}"
                                    type="button"
                                    ${e.unlocked?"":"disabled"}
                                    aria-label="${e.title} — ${e.unlocked?"Disponible":"Verrouillé"}, ${e.requirement}"
                                >
                                    <span data-title-style="${e.unlocked?e.style:""}">${e.title}</span>
                                    <span class="title-modal-status">
                                        ${e.unlocked?"":'<span aria-hidden="true">🔒</span> '}${e.requirement}
                                    </span>
                                </button>
                            `).join("")}

                    </div>

                </div>
            `;let l=i(t,n);t.addEventListener("click",e=>{let a=e.target.closest(".title-modal-item");a&&t.contains(a)&&!a.disabled&&l(a.dataset.title)}),t.addEventListener("click",e=>{e.target===t&&l()})})}export{d as titleModal};
