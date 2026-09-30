function s({title:d,message:a,confirmText:i="Confirmer",cancelText:l="Annuler",danger:t=!1}){return new Promise(m=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                        ${d}
                    </h3>

                    <p>
                        ${a}
                    </p>

                    <div class="confirm-modal-actions">

                        <button
                            class="confirm-modal-secondary"
                            type="button"
                        >
                            ${l}
                        </button>

                        <button
                            class="${t?"confirm-modal-danger":"confirm-modal-primary"}"
                            type="button"
                        >
                            ${i}
                        </button>

                    </div>

                </div>
            `;let r=t?".confirm-modal-danger":".confirm-modal-primary",n=o=>{document.body.contains(e)&&(document.body.style.overflow="",document.removeEventListener("keydown",c),e.remove(),m(o))},c=o=>{o.key==="Escape"&&n(!1)};document.body.append(e),document.body.style.overflow="hidden",document.addEventListener("keydown",c),e.querySelector(r)?.focus(),e.querySelector(".confirm-modal-secondary")?.addEventListener("click",()=>n(!1)),e.querySelector(r)?.addEventListener("click",()=>n(!0)),e.addEventListener("click",o=>{o.target===e&&n(!1)})})}export{s as a};
