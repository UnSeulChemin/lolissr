function f({title:a,message:l,confirmText:m="Confirmer",cancelText:i="Annuler",danger:n=!1}){return new Promise(s=>{let e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
                <div class="confirm-modal">

                    <h3>
                    </h3>

                    <p>
                    </p>

                    <div class="confirm-modal-actions">

                        <button
                            class="confirm-modal-secondary"
                            type="button"
                        >
                        </button>

                        <button
                            class="confirm-modal-primary"
                            type="button"
                        >
                        </button>

                    </div>

                </div>
            `,e.querySelector("h3").textContent=a??"",e.querySelector("p").textContent=l??"",e.querySelector(".confirm-modal-secondary").textContent=i;let r=e.querySelector(".confirm-modal-primary");r.textContent=m,n&&(r.className="confirm-modal-danger");let c=n?".confirm-modal-danger":".confirm-modal-primary",t=o=>{document.body.contains(e)&&(document.body.style.overflow="",document.removeEventListener("keydown",d),e.remove(),s(o))},d=o=>{o.key==="Escape"&&t(!1)};document.body.append(e),document.body.style.overflow="hidden",document.addEventListener("keydown",d),e.querySelector(c)?.focus(),e.querySelector(".confirm-modal-secondary")?.addEventListener("click",()=>t(!1)),e.querySelector(c)?.addEventListener("click",()=>t(!0)),e.addEventListener("click",o=>{o.target===e&&t(!1)})})}export{f as a};
