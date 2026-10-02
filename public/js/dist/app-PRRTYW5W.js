import{a as ce,b as ue,c as me,d as de,e as fe,f as pe,g as Ee,h as ye,i as Se,j as Ce,k as A}from"./chunks/chunk-3RVNBNKJ.js";import{a as ve}from"./chunks/chunk-HXYIU4QO.js";import"./chunks/chunk-G6XN5UE2.js";import{a as v}from"./chunks/chunk-PFV5AP25.js";import{a as T,b as le,d as B,f as K,g as U,h as q,i as j,j as V,k as ge,l as he,n as be}from"./chunks/chunk-ME5BFOWY.js";import{a as z,b as F,c as $e}from"./chunks/chunk-VUXMF5ZT.js";import{a as S,b as l,c as C,d as P,e as D,f as se}from"./chunks/chunk-NIXCKU33.js";async function Ae(e,t,r=()=>!0){let i=e.filter(([,o])=>o.isRelevant?.()??!0),n=await Promise.allSettled(i.map(([,o])=>o.preload?.()));for(let o=0;o<i.length;o++){if(!r())return;let[a,s]=i[o];await t(a,()=>{if(n[o].status==="rejected")throw n[o].reason;return s()})}}function Re(){window.addEventListener("unhandledrejection",e=>{v(e.reason)}),window.addEventListener("error",e=>{v(e.error)}),l("ERROR_HANDLER","initialized")}async function Te(e){if(!e)return!1;try{return await navigator.clipboard.writeText(e),T("Copié !","success"),!0}catch{return T("Impossible de copier","error"),!1}}var Pe=!1;function xe(){Pe||(Pe=!0,se(document,"click","[data-copy]",async(e,t)=>{let r=t.dataset.copy;await Te(r)}))}var ot=`
input,
textarea,
select,
[contenteditable="true"]
`,at=`
a,
button,
[role="button"]
`,we=!1,Q=!1;function Le(e,t){return e instanceof Element&&!!e.closest(t)}function st(e){return Le(e,ot)}function lt(e){return Le(e,at)}function Ie(){Q=!1}function ct(){Q=!0}function ut(){if(Q){l("BACKSPACE","blocked");return}if(ct(),l("BACKSPACE","navigate",location.pathname),window.history.length>1){window.history.back(),requestAnimationFrame(Ie);return}A(S.baseUri).finally(Ie)}function mt(e){e.key==="Backspace"&&(e.repeat||e.ctrlKey||e.metaKey||e.altKey||e.shiftKey||st(e.target)||lt(e.target)||(e.preventDefault(),ut()))}function Ne(){if(we){l("BACKSPACE","already-init");return}we=!0,document.addEventListener("keydown",mt,{passive:!1}),l("BACKSPACE","ready")}var dt=3,Z=0;async function De(e){if(!S.prefetch.enabled||navigator.connection?.saveData===!0)return null;let t=K(e);if(t===K(location.href)||V.has(t))return null;let r=ge(t);if(r)return l("PREFETCH","cache-hit",t),r;let i=be(t);if(i)return l("PREFETCH","reuse",t),i;if(l("PREFETCH","fetch",t),Z>=dt)return null;Z++;let n=new AbortController,o;return o=(async()=>{try{let a=await F(t,{timeout:S.prefetch.timeout,headers:{"X-Page-Format":"fragment",Accept:"application/json","X-Prefetch":"true","Cache-Control":"no-cache"},signal:n.signal});return a?.type!=="page"?(l("PREFETCH","invalid-response",t),null):n.signal.aborted||V.has(t)?(l("PREFETCH","skip-invalidated",t),null):(he(t,a),l("PREFETCH","success",t),a)}catch(a){return a?.name==="AbortError"?(l("PREFETCH","aborted",t),null):(C("PREFETCH",a),null)}finally{Z--,j.get(t)?.promise===o&&j.delete(t)}})(),j.set(t,{promise:o,controller:n}),o}function ft(e){if(!(e instanceof HTMLAnchorElement)||U(e)||e.hasAttribute("data-confirm-logout")||e.pathname.endsWith("/deconnexion")||e.dataset.prefetchBound==="true")return;e.dataset.prefetchBound="true";let t=null;e.addEventListener("pointerenter",()=>{clearTimeout(t),t=window.setTimeout(()=>{De(e.href)},S.prefetch.hoverDelay)},{passive:!0}),e.addEventListener("pointerleave",()=>{clearTimeout(t)},{passive:!0})}function X(){let e=document.querySelectorAll("a[data-prefetch]");for(let t of e)ft(t)}function ze(){!S.prefetch.enabled||q.initialized||(q.initialized=!0,X(),document.addEventListener("router:loaded",X),l("PREFETCH","ready"))}async function pt(e){if(e.defaultPrevented||e.button!==0||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;let t=e.target;if(!(t instanceof Element))return;let r=t.closest("a[href]");if(r instanceof HTMLAnchorElement){if(r.hasAttribute("data-confirm-logout")){if(e.preventDefault(),!await ve({title:"Déconnexion",message:"Êtes-vous sûr de vouloir vous déconnecter ?",confirmText:"Déconnexion"}))return;let n=await F(r.href,{method:"POST"});n?.type==="redirect"&&(window.location.href=n.redirect);return}U(r)||(e.preventDefault(),ye(),A(r.href))}}async function gt(){document.body.classList.add("no-route-animation"),await A(location.href,{updateHistory:!1,force:!0}),requestAnimationFrame(()=>{document.body.classList.remove("no-route-animation")})}function Ue(){Se(),history.scrollRestoration="manual",document.addEventListener("click",pt),window.addEventListener("popstate",gt),Ee(),l("ROUTER","ready")}var je=!1,W=null;function ht(){document.body.classList.add("is-routing")}function bt(){document.body.classList.remove("is-routing")}function Et(){clearTimeout(W),W=window.setTimeout(()=>{ht()},80),l("NAV_LOADING","start")}function Y(){clearTimeout(W),bt(),l("NAV_LOADING","end")}function Fe(){je||(je=!0,document.addEventListener(me,Et),document.addEventListener(de,Y),document.addEventListener(fe,Y),document.addEventListener(pe,Y),l("NAV_LOADING","initialized"))}var _e="router-debug-panel",He=!1;function ke(){return S.debug}function yt(){let e=document.createElement("div");return e.id=_e,e.innerHTML=`
        <div class="router-debug-title">
            DÉBOGAGE SPA
        </div>

        <div class="router-debug-content">
        </div>
    `,document.body.appendChild(e),e}function St(){return document.getElementById(_e)||yt()}function $t(e){if(!ke())return;let r=St().querySelector(".router-debug-content");if(!r)return;let i=document.createElement("div");for(i.textContent=`[${new Date().toLocaleTimeString()}] ${e}`,r.prepend(i);r.children.length>S.debugPanel.maxLogs;)r.lastChild?.remove()}function Ge(){He||(He=!0,ke()&&(["navigation:start","navigation:fetch","navigation:render","navigation:ready","navigation:error","navigation:abort"].forEach(e=>{document.addEventListener(e,t=>{$t(`${e} → ${t.detail?.to||""}`)})}),l("DEBUG_PANEL","initialized")))}async function Oe(e,t){try{return(await $e(e,{signal:t,headers:{Accept:"application/json"}}))?.data??{}}catch(r){throw r?.name==="AbortError"||t?.aborted?new DOMException("Search aborted","AbortError"):(C("SEARCH_API",r),r instanceof z&&(r.silent=!0),r)}}function d(e){return String(e??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function Ct(e){return String(e??"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}function x(e){return String(e??"").trim().toLowerCase()}function g(e,t){let r=String(e??""),i=x(t);if(i==="")return d(r);let n=i.split(/\s+/).filter(Boolean).map(Ct);if(n.length===0)return d(r);let o=new RegExp(`(${n.join("|")})`,"ig");return r.split(o).map((a,s)=>{let c=d(a);return s%2===1?`<mark class="search-highlight">${c}</mark>`:c}).join("")}var vt=Object.freeze([{symbol:"一",title:"HSK1",description:"Débutant total",url:"chinois/grammaire/hsk1"},{symbol:"二",title:"HSK2",description:"Bases simples",url:"chinois/grammaire/hsk2"},{symbol:"三",title:"HSK3",description:"Intermédiaire débutant",url:"chinois/grammaire/hsk3"},{symbol:"四",title:"HSK4",description:"Intermédiaire solide",url:"chinois/grammaire/hsk4"}]);function Me(e){let t=x(e).replaceAll(" ","");return t===""?[]:vt.filter(r=>[r.title,r.symbol].join(" ").toLowerCase().replaceAll(" ","").includes(t))}function Be(e){e?.classList.add("has-results")}function Ke(e){e?.classList.remove("has-results")}function H(e){e?.replaceChildren()}function h(e,t){let r=document.createElement("a");return r.href=e,r.className="search-result-item",r.innerHTML=t,r}function J(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.livre??"",a=e.thumbnail??"default",s=e.extension??"jpg",c=`${r}images/manga/thumbnail/${a}.${s}`,u=`${r}manga/series/${i}/${n}`;return h(u,`
            <img
                src="${c}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,t)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(n).padStart(2,"0")}
                </small>

            </span>
        `)}function ee(e,t){let r=e.id??"",i=e.type??"",n=e.titre??"",o=e.description??"",a=String(e.langue??"").toLowerCase(),s=String(e.niveau??"").toLowerCase(),c=i==="grammaire"?"📖":"📚",u=i==="grammaire"?s.toUpperCase():a==="jinyu"?"晋语":"中文",f=i==="grammaire"?`${t}chinois/grammaire/${s}/recherche/${r}`:`${t}chinois/vocabulaire/${a}/recherche/${r}`;return h(f,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${d(c)}
                </span>

                <span class="search-result-category-label">
                    ${d(u)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${d(n)}
                </strong>

                <small class="search-result-meta">
                    ${d(o)}
                </small>

            </span>
        `)}function te(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/figurine/thumbnail/${s}.${c}`,f=`${r}figurine/waifus/${i}/${n}`;return h(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function re(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/nendoroid/thumbnail/${s}.${c}`,f=`${r}nendoroid/waifus/${i}/${n}`;return h(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function ie(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/peluche/thumbnail/${s}.${c}`,f=`${r}peluche/waifus/${i}/${n}`;return h(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function ne(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.artbook??"",a=e.auteur??"",s=e.serie??"",c=e.thumbnail??"default",u=e.extension??"jpg",f=`${r}images/artbook/thumbnail/${c}.${u}`,b=`${r}manga/artbooks/${i}/${n}`,$=s||a||"Livre d’illustrations";return h(b,`
            <img
                src="${f}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${g($,t)}
                </small>

            </span>
        `)}function oe(e,t){let r=e.title??"",i=e.description??"",n=e.symbol??"→",o=e.url??"",a=`${t}${o}`;return h(a,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${d(n)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${d(r)}
                </strong>

                <small class="search-result-meta">
                    ${d(i)}
                </small>

            </span>
        `)}function qe(e,t){let r=document.createElement("div");r.className="header-search-section-title",r.textContent=t,e.appendChild(r)}function R({title:e,results:t,buildItem:r,searchInput:i,searchResults:n,searchDropdown:o,setupResultItem:a,index:s}){return t.length===0||(qe(n,e),t.forEach(c=>{let u=r(c);a(u,s,i,n,o),n.appendChild(u),s++})),s}function Ve({mangas:e,artbooks:t,chinois:r,figurines:i,nendoroids:n,peluches:o,shortcuts:a,rawValue:s,basePath:c,searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,openDropdown:O,closeDropdown:M}){H(f);let p=0;if(p=R({title:"📚 SÉRIES",results:e.slice(0,5),buildItem:E=>J(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"📕 ARTBOOKS",results:t.slice(0,5),buildItem:E=>ne(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"⛩️ CHINOIS",results:r.slice(0,5),buildItem:E=>ee(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:E=>te(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"🪆 NENDOROIDS",results:n.slice(0,5),buildItem:E=>re(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"🧸 PELUCHES",results:o.slice(0,5),buildItem:E=>ie(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p=R({title:"⚡ RACCOURCIS",results:a.slice(0,5),buildItem:E=>oe(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:$,index:p}),p===0){M(b);return}O(b)}function _(e,t){let r=D(".search-result-item",e);r.forEach(n=>{n.classList.remove("is-active")});let i=r[t];i&&(i.classList.add("is-active"),i.scrollIntoView({block:"nearest"}))}var w=0,I=null,At=200,k=null,L=null,y=-1;function Ze(){let e=P(".js-header-search"),t=P("#header-search-input"),r=P("#header-search-results"),i=P(".js-header-search-dropdown");!e||!t||!r||!i||e.dataset.initialized!=="true"&&(e.dataset.initialized="true",t.addEventListener("input",()=>{w++,L?.abort(),I=null,clearTimeout(k),k=setTimeout(()=>{Qe(e,t,r,i)},At)}),e.addEventListener("submit",n=>{n.preventDefault(),clearTimeout(k),Qe(e,t,r,i)}),t.addEventListener("keydown",n=>{Tt(n,t,r,i)}),document.addEventListener("click",n=>{n.target.closest(".js-header-search")||N(t,r,i)}))}async function Qe(e,t,r,i){let n=t.value,o=x(n);if(o===""){N(t,r,i);return}if(o===I)return;L?.abort(),I=o;let a=++w;L=new AbortController,y=-1;try{let s=e.dataset.basePath??"/",{mangas:c=[],artbooks:u=[],chinois:f=[],figurines:b=[],nendoroids:$=[],peluches:O=[]}=await Oe(`${s}recherche?q=${encodeURIComponent(o)}`,L.signal),M=Me(o);if(a!==w||t.value!==n)return;Ve({mangas:c,artbooks:u,chinois:f,figurines:b,nendoroids:$,peluches:O,shortcuts:M,rawValue:n,basePath:s,searchInput:t,searchResults:r,searchDropdown:i,setupResultItem:Rt,openDropdown:Pt,closeDropdown:Xe})}catch(s){if(a===w&&(I=null),s?.name==="AbortError")return}}function Rt(e,t,r,i,n){e.dataset.index=t,e.addEventListener("mouseenter",()=>{y=t,_(i,y)}),e.addEventListener("click",()=>{N(r,i,n)})}function Tt(e,t,r,i){let n=D(".search-result-item",r);if(e.key==="ArrowDown"){if(!n.length)return;e.preventDefault(),y++,y>=n.length&&(y=0),_(r,y);return}if(e.key==="ArrowUp"){if(!n.length)return;e.preventDefault(),y--,y<0&&(y=n.length-1),_(r,y);return}if(e.key==="Enter"){let o=n[y];o&&(e.preventDefault(),N(t,r,i),A(o.href))}e.key==="Escape"&&N(t,r,i)}function Pt(e){e.classList.remove("is-loading"),Be(e)}function Xe(e){Ke(e),e.classList.remove("is-loading")}function N(e,t,r){clearTimeout(k),w++,I=null,L?.abort(),y=-1,e.value="",H(t),Xe(r)}var Ye=[["Router",Ue],["Prefetch",ze],["Copy",xe],["GlobalSearchController",Ze],["NavigationLoading",Fe],["RouterDebugPanel",Ge],["GlobalBackNavigation",Ne],["GlobalErrorHandlers",Re]];function m(e,t,r=null){let i,n=()=>i??=e().catch(a=>{throw i=void 0,a}),o=async()=>{let s=(await n())[t];if(typeof s!="function")throw new TypeError(`Initialiseur "${t}" introuvable.`);await s()};return o.preload=n,o.isRelevant=()=>r===null||document.querySelector(r)!==null,o}var xt=m(()=>import("./chunks/create-DISEQNOV.js"),"initCreatePage"),wt=m(()=>import("./chunks/edit-LQLCHSR5.js"),"initEditPage"),It=m(()=>import("./chunks/update-note-GJSUTQBS.js"),"initUpdateNote",".js-note-button"),Lt=m(()=>import("./chunks/delete-manga-L5DHIAR4.js"),"initDeleteManga",".js-delete-manga"),Nt=m(()=>import("./chunks/delete-artbook-6PKLCVRE.js"),"initDeleteArtbook",".js-delete-artbook"),Dt=m(()=>import("./chunks/update-read-status-FLKWWGX5.js"),"initUpdateReadStatus",".js-read-status-button"),zt=m(()=>import("./chunks/create-6D23FRGV.js"),"initCreatePage"),Ut=m(()=>import("./chunks/delete-figurine-436AM2K6.js"),"initDeleteFigurine",".js-delete-figurine"),jt=m(()=>import("./chunks/update-collect-status-DLNNG3T5.js"),"initUpdateCollectStatus",".js-figurine-collect-status-button"),Ft=m(()=>import("./chunks/create-X6KBKR5L.js"),"initCreatePage"),Ht=m(()=>import("./chunks/delete-peluche-WMTLPMQU.js"),"initDeletePeluche",".js-delete-peluche"),_t=m(()=>import("./chunks/update-collect-status-LQIHGEPT.js"),"initUpdatePelucheCollectStatus",".js-peluche-collect-status-button"),kt=m(()=>import("./chunks/create-HIWWA2BO.js"),"initCreatePage"),Gt=m(()=>import("./chunks/delete-nendoroid-XKYVXJBG.js"),"initDeleteNendoroid",".js-delete-nendoroid"),Ot=m(()=>import("./chunks/update-collect-status-A55OOFXG.js"),"initUpdateNendoroidCollectStatus",".js-nendoroid-collect-status-button"),Mt=m(()=>import("./chunks/create-QHO3U4AO.js"),"initCreatePage"),Bt=m(()=>import("./chunks/flashcards-vocabulaire-TTQ2PU3L.js"),"initFlashcardsVocabulairePage"),Kt=m(()=>import("./chunks/flashcards-grammaire-VHJW53E5.js"),"initFlashcardsGrammairePage"),qt=m(()=>import("./chunks/toggle-grammar-mastery-FACMGJEA.js"),"initToggleGrammaireMaitrise",".grammar-ajax"),Vt=m(()=>import("./chunks/toggle-vocabulary-mastery-22SAYFE5.js"),"initToggleVocabulaireMaitrise",".vocabulary-ajax"),Qt=m(()=>import("./chunks/delete-grammar-YQYMDOLG.js"),"initDeleteGrammaire",".grammaire-delete"),Zt=m(()=>import("./chunks/delete-vocabulary-5XFE3DCM.js"),"initDeleteVocabulaire",".vocabulaire-delete"),Xt=m(()=>import("./chunks/profile-customization-2Y5454JC.js"),"initProfileCustomization"),Yt=m(()=>import("./chunks/sql-FNMCKZH4.js"),"initSqlPage"),We=[{match:/^\/manga(?:\/|$)/,initializers:[["UpdateNote",It],["DeleteManga",Lt],["DeleteArtbook",Nt],["UpdateReadStatus",Dt]]},{match:/^\/manga\/ajouter\/(manga|artbook)\/?$/,initializers:[["AjouterMangaPage",xt]]},{match:/^\/manga\/series\/.+\/modifier\/\d+\/?$/,initializers:[["ModifierMangaPage",wt]]},{match:/^\/figurine(?:\/|$)/,initializers:[["DeleteFigurine",Ut],["UpdateFigurineCollectStatus",jt]]},{match:/^\/figurine\/ajouter\/?$/,initializers:[["AjouterFigurinePage",zt]]},{match:/^\/peluche(?:\/|$)/,initializers:[["DeletePeluche",Ht],["UpdatePelucheCollectStatus",_t]]},{match:/^\/peluche\/ajouter\/?$/,initializers:[["AjouterPeluchePage",Ft]]},{match:/^\/nendoroid(?:\/|$)/,initializers:[["DeleteNendoroid",Gt],["UpdateNendoroidCollectStatus",Ot]]},{match:/^\/nendoroid\/ajouter\/?$/,initializers:[["AjouterNendoroidPage",kt]]},{match:/^\/chinois(?:\/|$)/,initializers:[["ToggleGrammaireMaitrise",qt],["ToggleVocabulaireMaitrise",Vt],["DeleteGrammaire",Qt],["DeleteVocabulaire",Zt]]},{match:/^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,initializers:[["AjouterChinoisPage",Mt]]},{match:/^\/chinois\/flashcards\/vocabulaire\/?$/,initializers:[["FlashcardsVocabulaire",Bt]]},{match:/^\/chinois\/flashcards\/grammaire\/?$/,initializers:[["FlashcardsGrammaire",Kt]]},{match:/^\/profil\/personnalisation\/?$/,initializers:[["ProfileCustomization",Xt]]},{match:/^\/sql\/?$/,initializers:[["SqlPage",Yt]]}];function Je(){window.setTimeout(()=>{location.reload()},300)}function et(){window.location.hostname.includes("localhost")&&(window.enableDebug=()=>{localStorage.setItem("lolissr_debug","1"),T("Debug activé","success"),Je()},window.disableDebug=()=>{localStorage.removeItem("lolissr_debug"),T("Debug désactivé","success"),Je()},window.__TEST_ERROR__=()=>{throw new Error("Test error")},window.__TEST_PROMISE_ERROR__=()=>{Promise.reject(new Error("Promise test error"))})}async function ae(e,t){ce(e);try{await t(),l("INIT",`✅ ${e}`)}catch(r){C("INIT",r),v(r instanceof Error?r:new z(`Erreur pendant "${e}"`,{cause:r}))}finally{ue(e)}}async function Wt(){for(let[e,t]of Ye)await ae(e,t)}var G=0;async function tt(){let e=++G,t=B(),r=We.filter(({match:i})=>i.test(t)).flatMap(({initializers:i})=>i);await Ae(r,ae,()=>e===G&&t===B())}async function rt(){l("APP","🚀 Boot"),et();let e=G;Ce(tt),await Wt(),G===e&&await tt(),await ae("FlashToast",le),l("APP","✅ Ready")}function it(){rt().catch(e=>{C("APP",e),v(e)})}function nt(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",it,{once:!0});return}it()}nt();
