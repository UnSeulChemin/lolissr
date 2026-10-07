import{a as m}from"./chunk-MOUFMENJ.js";var n=null;function C({title:u,message:f,confirmText:y="Confirmer",cancelText:v="Annuler",danger:r=!1}){return new Promise(p=>{n?.(!1);let b=document.body.style.overflow,c=document.activeElement,l=!1,s=()=>{},e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
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
            `,e.querySelector("h3").textContent=u??"",e.querySelector("p").textContent=f??"",e.querySelector(".confirm-modal-secondary").textContent=v;let a=e.querySelector(".confirm-modal-primary");a.textContent=y,r&&(a.className="confirm-modal-danger");let i=r?".confirm-modal-danger":".confirm-modal-primary",o=t=>{l||(l=!0,s(),n===o&&(n=null),document.body.style.overflow=b,document.removeEventListener("keydown",d),e.remove(),c?.isConnected&&c.focus(),p(t))},d=t=>{t.key==="Escape"&&o(!1)};n=o,s=m(()=>o(!1)),document.body.append(e),document.body.style.overflow="hidden",document.addEventListener("keydown",d),e.querySelector(i)?.focus(),e.querySelector(".confirm-modal-secondary")?.addEventListener("click",()=>o(!1)),e.querySelector(i)?.addEventListener("click",()=>o(!0)),e.addEventListener("click",t=>{t.target===e&&o(!1)})})}export{C as a};
