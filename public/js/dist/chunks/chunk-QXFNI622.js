import{a as u}from"./chunk-MOUFMENJ.js";var i=null;function f(n,d){i?.();let t=document.activeElement,a=document.body.style.overflow,e=n.querySelector(".confirm-modal");e.setAttribute("role","dialog"),e.setAttribute("aria-modal","true"),e.setAttribute("aria-label",e.querySelector("h3")?.textContent.trim()??"Personnalisation"),e.tabIndex=-1;let s=!1,c=()=>{},l=(o=null)=>{s||(s=!0,c(),document.removeEventListener("keydown",m,!0),n.remove(),document.body.style.overflow=a,i===l&&(i=null),t?.isConnected&&t.focus(),d(o))},m=o=>{if(o.key==="Escape"&&(o.preventDefault(),o.stopImmediatePropagation(),l()),o.key==="Tab"){let r=[...e.querySelectorAll("button:not(:disabled)")],p=r.indexOf(document.activeElement);o.preventDefault(),r.length?r[(p+(o.shiftKey?-1:1)+r.length)%r.length].focus():e.focus()}};return i=l,c=u(()=>l()),document.body.append(n),document.body.style.overflow="hidden",document.addEventListener("keydown",m,!0),(e.querySelector("button")??e).focus(),l}function y(n){return new Promise(d=>{let t=document.createElement("div");t.className="confirm-modal-overlay title-modal-overlay",t.innerHTML=`
                <div class="confirm-modal title-modal" role="dialog" aria-modal="true" aria-label="Choisir un titre">

                    <h3>
                        Choisir un titre
                    </h3>

                    <div class="title-modal-list">

                        ${n.map(e=>`
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
            `;let a=f(t,d);t.querySelectorAll(".title-modal-item:not(:disabled)").forEach(e=>{e.addEventListener("click",()=>a(e.dataset.title))}),t.addEventListener("click",e=>{e.target===t&&a()})})}export{f as a,y as b};
