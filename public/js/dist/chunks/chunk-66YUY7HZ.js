import{a as p}from"./chunk-MOUFMENJ.js";var l=null;function C({title:c,message:b,confirmText:v="Confirmer",cancelText:g="Annuler",danger:a=!1}){return new Promise(h=>{l?.(!1);let E=document.body.style.overflow,i=document.activeElement,s=!1,d=()=>{},e=document.createElement("div");e.className="confirm-modal-overlay",e.innerHTML=`
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
            `,e.querySelector("h3").textContent=c??"";let r=e.querySelector(".confirm-modal");r.setAttribute("role","dialog"),r.setAttribute("aria-modal","true"),r.setAttribute("aria-label",c||"Confirmation"),e.querySelector("p").textContent=b??"",e.querySelector(".confirm-modal-secondary").textContent=g;let m=e.querySelector(".confirm-modal-primary");m.textContent=v,a&&(m.className="confirm-modal-danger");let u=a?".confirm-modal-danger":".confirm-modal-primary",o=t=>{s||(s=!0,d(),l===o&&(l=null),document.body.style.overflow=E,document.removeEventListener("keydown",f,!0),e.remove(),i?.isConnected&&i.focus(),h(t))},f=t=>{if(t.key==="Escape"&&(t.preventDefault(),t.stopImmediatePropagation(),o(!1)),t.key==="Tab"){t.preventDefault(),t.stopImmediatePropagation();let n=[...r.querySelectorAll("button:not(:disabled)")],y=n.indexOf(document.activeElement),S=y<0?t.shiftKey?n.length-1:0:(y+(t.shiftKey?-1:1)+n.length)%n.length;n[S]?.focus()}};l=o,d=p(()=>o(!1)),document.body.append(e),document.body.style.overflow="hidden",document.addEventListener("keydown",f,!0),e.querySelector(u)?.focus(),e.querySelector(".confirm-modal-secondary")?.addEventListener("click",()=>o(!1)),e.querySelector(u)?.addEventListener("click",()=>o(!0)),e.addEventListener("click",t=>{t.target===e&&o(!1)})})}export{C as a};
