import{a as ce,b as ue,c as me,d as de,e as fe,f as pe,g as Ee,h as ye,i as Se,j as $e,k as R}from"./chunks/chunk-6RGEESTW.js";import{a as ve}from"./chunks/chunk-HXYIU4QO.js";import"./chunks/chunk-G6XN5UE2.js";import{a as v}from"./chunks/chunk-PFV5AP25.js";import{a as C,b as le,d as B,f as K,g as z,h as q,i as U,j as V,k as ge,l as he,n as be}from"./chunks/chunk-ME5BFOWY.js";import{a as j,b as F,c as Ae}from"./chunks/chunk-VUXMF5ZT.js";import{a as S,b as l,c as $,d as P,e as D,f as se}from"./chunks/chunk-NIXCKU33.js";async function Re(e,t,r=()=>!0){let i=e.filter(([,n])=>n.isRelevant?.()??!0),o=await Promise.allSettled(i.map(([,n])=>n.preload?.()));for(let n=0;n<i.length;n++){if(!r())return;let[a,s]=i[n];await t(a,()=>{if(o[n].status==="rejected")throw o[n].reason;return s()})}}function Te(){window.addEventListener("unhandledrejection",e=>{v(e.reason)}),window.addEventListener("error",e=>{v(e.error)}),l("ERROR_HANDLER","initialized")}async function Ce(e){if(!e)return!1;try{return await navigator.clipboard.writeText(e),C("Copié !","success"),!0}catch{return C("Impossible de copier","error"),!1}}var Pe=!1;function xe(){Pe||(Pe=!0,se(document,"click","[data-copy]",async(e,t)=>{let r=t.dataset.copy;await Ce(r)}))}var nt=`
input,
textarea,
select,
[contenteditable="true"]
`,at=`
a,
button,
[role="button"]
`,we=!1,Q=!1;function Le(e,t){return e instanceof Element&&!!e.closest(t)}function st(e){return Le(e,nt)}function lt(e){return Le(e,at)}function Ie(){Q=!1}function ct(){Q=!0}function ut(){if(Q){l("BACKSPACE","blocked");return}if(ct(),l("BACKSPACE","navigate",location.pathname),window.history.length>1){window.history.back(),requestAnimationFrame(Ie);return}R(S.baseUri).finally(Ie)}function mt(e){e.key==="Backspace"&&(e.repeat||e.ctrlKey||e.metaKey||e.altKey||e.shiftKey||st(e.target)||lt(e.target)||(e.preventDefault(),ut()))}function Ne(){if(we){l("BACKSPACE","already-init");return}we=!0,document.addEventListener("keydown",mt,{passive:!1}),l("BACKSPACE","ready")}var dt=3,Z=0;async function De(e){if(!S.prefetch.enabled||navigator.connection?.saveData===!0)return null;let t=K(e);if(t===K(location.href)||V.has(t))return null;let r=ge(t);if(r)return l("PREFETCH","cache-hit",t),r;let i=be(t);if(i)return l("PREFETCH","reuse",t),i;if(l("PREFETCH","fetch",t),Z>=dt)return null;Z++;let o=new AbortController,n;return n=(async()=>{try{let a=await F(t,{timeout:S.prefetch.timeout,headers:{"X-Page-Format":"fragment",Accept:"application/json","X-Prefetch":"true","Cache-Control":"no-cache"},signal:o.signal});return a?.type!=="page"?(l("PREFETCH","invalid-response",t),null):o.signal.aborted||V.has(t)?(l("PREFETCH","skip-invalidated",t),null):(he(t,a),l("PREFETCH","success",t),a)}catch(a){return a?.name==="AbortError"?(l("PREFETCH","aborted",t),null):($("PREFETCH",a),null)}finally{Z--,U.get(t)?.promise===n&&U.delete(t)}})(),U.set(t,{promise:n,controller:o}),n}function ft(e){if(!(e instanceof HTMLAnchorElement)||z(e)||e.hasAttribute("data-confirm-logout")||e.pathname.endsWith("/deconnexion")||e.dataset.prefetchBound==="true")return;e.dataset.prefetchBound="true";let t=null;e.addEventListener("pointerenter",()=>{clearTimeout(t),t=window.setTimeout(()=>{De(e.href)},S.prefetch.hoverDelay)},{passive:!0}),e.addEventListener("pointerleave",()=>{clearTimeout(t)},{passive:!0})}function X(){let e=document.querySelectorAll("a[data-prefetch]");for(let t of e)ft(t)}function je(){!S.prefetch.enabled||q.initialized||(q.initialized=!0,X(),document.addEventListener("router:loaded",X),l("PREFETCH","ready"))}async function pt(e){if(e.defaultPrevented||e.button!==0||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;let t=e.target;if(!(t instanceof Element))return;let r=t.closest("a[href]");if(r instanceof HTMLAnchorElement){if(r.hasAttribute("data-confirm-logout")){if(e.preventDefault(),!await ve({title:"Déconnexion",message:"Êtes-vous sûr de vouloir vous déconnecter ?",confirmText:"Déconnexion"}))return;let o=await F(r.href,{method:"POST"});o?.type==="redirect"&&(window.location.href=o.redirect);return}z(r)||(e.preventDefault(),ye(),R(r.href))}}async function gt(){document.body.classList.add("no-route-animation"),await R(location.href,{updateHistory:!1,force:!0}),requestAnimationFrame(()=>{document.body.classList.remove("no-route-animation")})}function ze(){Se(),history.scrollRestoration="manual",document.addEventListener("click",pt),window.addEventListener("popstate",gt),Ee(),l("ROUTER","ready")}var Ue=!1,W=null;function ht(){document.body.classList.add("is-routing")}function bt(){document.body.classList.remove("is-routing")}function Et(){clearTimeout(W),W=window.setTimeout(()=>{ht()},80),l("NAV_LOADING","start")}function Y(){clearTimeout(W),bt(),l("NAV_LOADING","end")}function Fe(){Ue||(Ue=!0,document.addEventListener(me,Et),document.addEventListener(de,Y),document.addEventListener(fe,Y),document.addEventListener(pe,Y),l("NAV_LOADING","initialized"))}var ke="router-debug-panel",He=!1;function _e(){return S.debug}function yt(){let e=document.createElement("div");return e.id=ke,e.innerHTML=`
        <div class="router-debug-title">
            SPA DEBUG
        </div>

        <div class="router-debug-content">
        </div>
    `,document.body.appendChild(e),e}function St(){return document.getElementById(ke)||yt()}function At(e){if(!_e())return;let r=St().querySelector(".router-debug-content");if(!r)return;let i=document.createElement("div");for(i.textContent=`[${new Date().toLocaleTimeString()}] ${e}`,r.prepend(i);r.children.length>S.debugPanel.maxLogs;)r.lastChild?.remove()}function Oe(){He||(He=!0,_e()&&(["navigation:start","navigation:fetch","navigation:render","navigation:ready","navigation:error","navigation:abort"].forEach(e=>{document.addEventListener(e,t=>{At(`${e} → ${t.detail?.to||""}`)})}),l("DEBUG_PANEL","initialized")))}async function Ge(e,t){try{return(await Ae(e,{signal:t,headers:{Accept:"application/json"}}))?.data??{}}catch(r){throw r?.name==="AbortError"||t?.aborted?new DOMException("Search aborted","AbortError"):($("SEARCH_API",r),r instanceof j&&(r.silent=!0),r)}}function d(e){return String(e??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function $t(e){return String(e??"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}function x(e){return String(e??"").trim().toLowerCase()}function g(e,t){let r=String(e??""),i=x(t);if(i==="")return d(r);let o=i.split(/\s+/).filter(Boolean).map($t);if(o.length===0)return d(r);let n=new RegExp(`(${o.join("|")})`,"ig");return r.split(n).map((a,s)=>{let c=d(a);return s%2===1?`<mark class="search-highlight">${c}</mark>`:c}).join("")}var vt=Object.freeze([{symbol:"一",title:"HSK1",description:"Débutant total",url:"chinois/grammaire/hsk1"},{symbol:"二",title:"HSK2",description:"Bases simples",url:"chinois/grammaire/hsk2"},{symbol:"三",title:"HSK3",description:"Intermédiaire débutant",url:"chinois/grammaire/hsk3"},{symbol:"四",title:"HSK4",description:"Intermédiaire solide",url:"chinois/grammaire/hsk4"}]);function Me(e){let t=x(e).replaceAll(" ","");return t===""?[]:vt.filter(r=>[r.title,r.symbol].join(" ").toLowerCase().replaceAll(" ","").includes(t))}function Be(e){e?.classList.add("has-results")}function Ke(e){e?.classList.remove("has-results")}function H(e){e?.replaceChildren()}function h(e,t){let r=document.createElement("a");return r.href=e,r.className="search-result-item",r.innerHTML=t,r}function J(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.livre??"",a=e.thumbnail??"default",s=e.extension??"jpg",c=`${r}images/manga/thumbnail/${a}.${s}`,u=`${r}manga/series/${i}/${o}`;return h(u,`
            <img
                src="${c}"
                alt="${d(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,t)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(o).padStart(2,"0")}
                </small>

            </span>
        `)}function ee(e,t){let r=e.id??"",i=e.type??"",o=e.titre??"",n=e.description??"",a=String(e.langue??"").toLowerCase(),s=String(e.niveau??"").toLowerCase(),c=i==="grammaire"?"📖":"📚",u=i==="grammaire"?s.toUpperCase():a==="jinyu"?"晋语":"中文",f=i==="grammaire"?`${t}chinois/grammaire/${s}/recherche/${r}`:`${t}chinois/vocabulaire/${a}/recherche/${r}`;return h(f,`
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
                    ${d(o)}
                </strong>

                <small class="search-result-meta">
                    ${d(n)}
                </small>

            </span>
        `)}function te(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/figurine/thumbnail/${s}.${c}`,f=`${r}figurine/waifus/${i}/${o}`;return h(f,`
            <img
                src="${u}"
                alt="${d(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function re(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/nendoroid/thumbnail/${s}.${c}`,f=`${r}nendoroid/waifus/${i}/${o}`;return h(f,`
            <img
                src="${u}"
                alt="${d(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function ie(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/peluche/thumbnail/${s}.${c}`,f=`${r}peluche/waifus/${i}/${o}`;return h(f,`
            <img
                src="${u}"
                alt="${d(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,t)}
                </small>

            </span>
        `)}function oe(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.artbook??"",a=e.auteur??"",s=e.serie??"",c=e.thumbnail??"default",u=e.extension??"jpg",f=`${r}images/artbook/thumbnail/${c}.${u}`,b=`${r}manga/artbooks/${i}/${o}`,A=s||a||"Artbook";return h(b,`
            <img
                src="${f}"
                alt="${d(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,t)}
                </strong>

                <small class="search-result-meta">
                    ${g(A,t)}
                </small>

            </span>
        `)}function ne(e,t){let r=e.title??"",i=e.description??"",o=e.symbol??"→",n=e.url??"",a=`${t}${n}`;return h(a,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${d(o)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${d(r)}
                </strong>

                <small class="search-result-meta">
                    ${d(i)}
                </small>

            </span>
        `)}function qe(e,t){let r=document.createElement("div");r.className="header-search-section-title",r.textContent=t,e.appendChild(r)}function T({title:e,results:t,buildItem:r,searchInput:i,searchResults:o,searchDropdown:n,setupResultItem:a,index:s}){return t.length===0||(qe(o,e),t.forEach(c=>{let u=r(c);a(u,s,i,o,n),o.appendChild(u),s++})),s}function Ve({mangas:e,artbooks:t,chinois:r,figurines:i,nendoroids:o,peluches:n,shortcuts:a,rawValue:s,basePath:c,searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,openDropdown:G,closeDropdown:M}){H(f);let p=0;if(p=T({title:"📚 SÉRIES",results:e.slice(0,5),buildItem:E=>J(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"📕 ARTBOOKS",results:t.slice(0,5),buildItem:E=>oe(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"⛩️ CHINOIS",results:r.slice(0,5),buildItem:E=>ee(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:E=>te(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"🪆 NENDOROIDS",results:o.slice(0,5),buildItem:E=>re(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"🧸 PELUCHES",results:n.slice(0,5),buildItem:E=>ie(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p=T({title:"⚡ RACCOURCIS",results:a.slice(0,5),buildItem:E=>ne(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:A,index:p}),p===0){M(b);return}G(b)}function k(e,t){let r=D(".search-result-item",e);r.forEach(o=>{o.classList.remove("is-active")});let i=r[t];i&&(i.classList.add("is-active"),i.scrollIntoView({block:"nearest"}))}var w=0,I=null,Rt=200,_=null,L=null,y=-1;function Ze(){let e=P(".js-header-search"),t=P("#header-search-input"),r=P("#header-search-results"),i=P(".js-header-search-dropdown");!e||!t||!r||!i||e.dataset.initialized!=="true"&&(e.dataset.initialized="true",t.addEventListener("input",()=>{w++,L?.abort(),I=null,clearTimeout(_),_=setTimeout(()=>{Qe(e,t,r,i)},Rt)}),e.addEventListener("submit",o=>{o.preventDefault(),clearTimeout(_),Qe(e,t,r,i)}),t.addEventListener("keydown",o=>{Ct(o,t,r,i)}),document.addEventListener("click",o=>{o.target.closest(".js-header-search")||N(t,r,i)}))}async function Qe(e,t,r,i){let o=t.value,n=x(o);if(n===""){N(t,r,i);return}if(n===I)return;L?.abort(),I=n;let a=++w;L=new AbortController,y=-1;try{let s=e.dataset.basePath??"/",{mangas:c=[],artbooks:u=[],chinois:f=[],figurines:b=[],nendoroids:A=[],peluches:G=[]}=await Ge(`${s}recherche?q=${encodeURIComponent(n)}`,L.signal),M=Me(n);if(a!==w||t.value!==o)return;Ve({mangas:c,artbooks:u,chinois:f,figurines:b,nendoroids:A,peluches:G,shortcuts:M,rawValue:o,basePath:s,searchInput:t,searchResults:r,searchDropdown:i,setupResultItem:Tt,openDropdown:Pt,closeDropdown:Xe})}catch(s){if(a===w&&(I=null),s?.name==="AbortError")return}}function Tt(e,t,r,i,o){e.dataset.index=t,e.addEventListener("mouseenter",()=>{y=t,k(i,y)}),e.addEventListener("click",()=>{N(r,i,o)})}function Ct(e,t,r,i){let o=D(".search-result-item",r);if(e.key==="ArrowDown"){if(!o.length)return;e.preventDefault(),y++,y>=o.length&&(y=0),k(r,y);return}if(e.key==="ArrowUp"){if(!o.length)return;e.preventDefault(),y--,y<0&&(y=o.length-1),k(r,y);return}if(e.key==="Enter"){let n=o[y];n&&(e.preventDefault(),N(t,r,i),R(n.href))}e.key==="Escape"&&N(t,r,i)}function Pt(e){e.classList.remove("is-loading"),Be(e)}function Xe(e){Ke(e),e.classList.remove("is-loading")}function N(e,t,r){clearTimeout(_),w++,I=null,L?.abort(),y=-1,e.value="",H(t),Xe(r)}var Ye=[["Router",ze],["Prefetch",je],["Copy",xe],["SearchController",Ze],["NavigationLoading",Fe],["RouterDebugPanel",Oe],["GlobalBackNavigation",Ne],["GlobalErrorHandlers",Te]];function m(e,t,r=null){let i,o=()=>i??=e().catch(a=>{throw i=void 0,a}),n=async()=>{let s=(await o())[t];if(typeof s!="function")throw new TypeError(`Initialiseur "${t}" introuvable.`);await s()};return n.preload=o,n.isRelevant=()=>r===null||document.querySelector(r)!==null,n}var xt=m(()=>import("./chunks/ajouter-IKP462NS.js"),"initAjouterPage"),wt=m(()=>import("./chunks/modifier-Y6FDPJ3M.js"),"initModifierPage"),It=m(()=>import("./chunks/update-note-GJSUTQBS.js"),"initUpdateNote",".js-note-button"),Lt=m(()=>import("./chunks/delete-manga-TD426PLU.js"),"initDeleteManga",".js-delete-manga"),Nt=m(()=>import("./chunks/delete-artbook-PF76UMBT.js"),"initDeleteArtbook",".js-delete-artbook"),Dt=m(()=>import("./chunks/update-read-status-WJTEIGZC.js"),"initUpdateReadStatus",".js-read-status-button"),jt=m(()=>import("./chunks/ajouter-M5XLXKBQ.js"),"initAjouterPage"),zt=m(()=>import("./chunks/delete-figurine-OBZ4KNHW.js"),"initDeleteFigurine",".js-delete-figurine"),Ut=m(()=>import("./chunks/update-collect-status-GZLQ64BL.js"),"initUpdateCollectStatus",".js-figurine-collect-status-button"),Ft=m(()=>import("./chunks/ajouter-USKOEAX7.js"),"initAjouterPage"),Ht=m(()=>import("./chunks/delete-peluche-NPVVIEG5.js"),"initDeletePeluche",".js-delete-peluche"),kt=m(()=>import("./chunks/update-collect-status-HWQZXCGI.js"),"initUpdatePelucheCollectStatus",".js-peluche-collect-status-button"),_t=m(()=>import("./chunks/ajouter-RK3OWHU3.js"),"initAjouterPage"),Ot=m(()=>import("./chunks/delete-nendoroid-63BKN6N4.js"),"initDeleteNendoroid",".js-delete-nendoroid"),Gt=m(()=>import("./chunks/update-collect-status-QRHJ763I.js"),"initUpdateNendoroidCollectStatus",".js-nendoroid-collect-status-button"),Mt=m(()=>import("./chunks/ajouter-VSPLJKQT.js"),"initAjouterPage"),Bt=m(()=>import("./chunks/flashcards-vocabulaire-4G4K6SU6.js"),"initFlashcardsVocabulairePage"),Kt=m(()=>import("./chunks/flashcards-grammaire-VLBLDYFF.js"),"initFlashcardsGrammairePage"),qt=m(()=>import("./chunks/toggle-grammar-mastery-FACMGJEA.js"),"initToggleGrammaireMaitrise",".grammar-ajax"),Vt=m(()=>import("./chunks/toggle-vocabulary-mastery-22SAYFE5.js"),"initToggleVocabulaireMaitrise",".vocabulary-ajax"),Qt=m(()=>import("./chunks/delete-grammar-XTRLEUYF.js"),"initDeleteGrammaire",".grammaire-delete"),Zt=m(()=>import("./chunks/delete-vocabulary-M6AQYLQV.js"),"initDeleteVocabulaire",".vocabulaire-delete"),Xt=m(()=>import("./chunks/profile-customization-MALRSDJO.js"),"initProfileCustomization"),Yt=m(()=>import("./chunks/sql-FNMCKZH4.js"),"initSqlPage"),We=[{match:/^\/manga(?:\/|$)/,initializers:[["UpdateNote",It],["DeleteManga",Lt],["DeleteArtbook",Nt],["UpdateReadStatus",Dt]]},{match:/^\/manga\/ajouter\/(manga|artbook)\/?$/,initializers:[["AjouterMangaPage",xt]]},{match:/^\/manga\/series\/.+\/modifier\/\d+\/?$/,initializers:[["ModifierMangaPage",wt]]},{match:/^\/figurine(?:\/|$)/,initializers:[["DeleteFigurine",zt],["UpdateFigurineCollectStatus",Ut]]},{match:/^\/figurine\/ajouter\/?$/,initializers:[["AjouterFigurinePage",jt]]},{match:/^\/peluche(?:\/|$)/,initializers:[["DeletePeluche",Ht],["UpdatePelucheCollectStatus",kt]]},{match:/^\/peluche\/ajouter\/?$/,initializers:[["AjouterPeluchePage",Ft]]},{match:/^\/nendoroid(?:\/|$)/,initializers:[["DeleteNendoroid",Ot],["UpdateNendoroidCollectStatus",Gt]]},{match:/^\/nendoroid\/ajouter\/?$/,initializers:[["AjouterNendoroidPage",_t]]},{match:/^\/chinois(?:\/|$)/,initializers:[["ToggleGrammaireMaitrise",qt],["ToggleVocabulaireMaitrise",Vt],["DeleteGrammaire",Qt],["DeleteVocabulaire",Zt]]},{match:/^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,initializers:[["AjouterChinoisPage",Mt]]},{match:/^\/chinois\/flashcards\/vocabulaire\/?$/,initializers:[["FlashcardsVocabulaire",Bt]]},{match:/^\/chinois\/flashcards\/grammaire\/?$/,initializers:[["FlashcardsGrammaire",Kt]]},{match:/^\/profil\/personnalisation\/?$/,initializers:[["ProfileCustomization",Xt]]},{match:/^\/sql\/?$/,initializers:[["SqlPage",Yt]]}];function Je(){window.setTimeout(()=>{location.reload()},300)}function et(){window.location.hostname.includes("localhost")&&(window.enableDebug=()=>{localStorage.setItem("lolissr_debug","1"),C("Debug activé","success"),Je()},window.disableDebug=()=>{localStorage.removeItem("lolissr_debug"),C("Debug désactivé","success"),Je()},window.__TEST_ERROR__=()=>{throw new Error("Test error")},window.__TEST_PROMISE_ERROR__=()=>{Promise.reject(new Error("Promise test error"))})}async function ae(e,t){ce(e);try{await t(),l("INIT",`✅ ${e}`)}catch(r){$("INIT",r),v(r instanceof Error?r:new j(`Erreur pendant "${e}"`,{cause:r}))}finally{ue(e)}}async function Wt(){for(let[e,t]of Ye)await ae(e,t)}var O=0;async function tt(){let e=++O,t=B(),r=We.filter(({match:i})=>i.test(t)).flatMap(({initializers:i})=>i);await Re(r,ae,()=>e===O&&t===B())}async function rt(){l("APP","🚀 Boot"),et();let e=O;$e(tt),await Wt(),O===e&&await tt(),await ae("FlashToast",le),l("APP","✅ Ready")}function it(){rt().catch(e=>{$("APP",e),v(e)})}function ot(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",it,{once:!0});return}it()}ot();
