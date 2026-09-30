import{a as le,b as ce,c as ue,d as me,e as de,f as fe,g as be,h as Ee,i as Ae,j as T}from"./chunks/chunk-A6JGQHIF.js";import{a as Se}from"./chunks/chunk-HXYIU4QO.js";import"./chunks/chunk-G6XN5UE2.js";import{a as R}from"./chunks/chunk-3FJOJDAW.js";import{a as P,b as se,d as M,f as B,g as z,h as K,i as U,j as q,k as pe,l as ge,n as he}from"./chunks/chunk-IQWFBYIB.js";import{a as j,b as F,c as ye}from"./chunks/chunk-VUXMF5ZT.js";import{a as A,b as l,c as $,d as C,e as D,f as ae}from"./chunks/chunk-NIXCKU33.js";async function $e(e,t,r=()=>!0){let i=e.filter(([,n])=>n.isRelevant?.()??!0),o=await Promise.allSettled(i.map(([,n])=>n.preload?.()));for(let n=0;n<i.length;n++){if(!r())return;let[a,s]=i[n];await t(a,()=>{if(o[n].status==="rejected")throw o[n].reason;return s()})}}function Re(){window.addEventListener("unhandledrejection",e=>{R(e.reason)}),window.addEventListener("error",e=>{R(e.error)}),l("ERROR_HANDLER","initialized")}async function Te(e){if(!e)return!1;try{return await navigator.clipboard.writeText(e),P("Copié !","success"),!0}catch{return P("Impossible de copier","error"),!1}}var ve=!1;function Pe(){ve||(ve=!0,ae(document,"click","[data-copy]",async(e,t)=>{let r=t.dataset.copy;await Te(r)}))}var ot=`
input,
textarea,
select,
[contenteditable="true"]
`,nt=`
a,
button,
[role="button"]
`,Ce=!1,V=!1;function we(e,t){return e instanceof Element&&!!e.closest(t)}function at(e){return we(e,ot)}function st(e){return we(e,nt)}function xe(){V=!1}function lt(){V=!0}function ct(){if(V){l("BACKSPACE","blocked");return}if(lt(),l("BACKSPACE","navigate",location.pathname),window.history.length>1){window.history.back(),requestAnimationFrame(xe);return}T(A.baseUri).finally(xe)}function ut(e){e.key==="Backspace"&&(e.repeat||e.ctrlKey||e.metaKey||e.altKey||e.shiftKey||at(e.target)||st(e.target)||(e.preventDefault(),ct()))}function Ie(){if(Ce){l("BACKSPACE","already-init");return}Ce=!0,document.addEventListener("keydown",ut,{passive:!1}),l("BACKSPACE","ready")}var mt=3,Q=0;async function Le(e){if(!A.prefetch.enabled||navigator.connection?.saveData===!0)return null;let t=B(e);if(t===B(location.href)||q.has(t))return null;let r=pe(t);if(r)return l("PREFETCH","cache-hit",t),r;let i=he(t);if(i)return l("PREFETCH","reuse",t),i;if(l("PREFETCH","fetch",t),Q>=mt)return null;Q++;let o=new AbortController,n;return n=(async()=>{try{let a=await F(t,{timeout:A.prefetch.timeout,headers:{"X-Page-Format":"fragment",Accept:"application/json","X-Prefetch":"true","Cache-Control":"no-cache"},signal:o.signal});return a?.type!=="page"?(l("PREFETCH","invalid-response",t),null):o.signal.aborted||q.has(t)?(l("PREFETCH","skip-invalidated",t),null):(ge(t,a),l("PREFETCH","success",t),a)}catch(a){return a?.name==="AbortError"?(l("PREFETCH","aborted",t),null):($("PREFETCH",a),null)}finally{Q--,U.get(t)?.promise===n&&U.delete(t)}})(),U.set(t,{promise:n,controller:o}),n}function dt(e){if(!(e instanceof HTMLAnchorElement)||z(e)||e.hasAttribute("data-confirm-logout")||e.pathname.endsWith("/deconnexion")||e.dataset.prefetchBound==="true")return;e.dataset.prefetchBound="true";let t=null;e.addEventListener("pointerenter",()=>{clearTimeout(t),t=window.setTimeout(()=>{Le(e.href)},A.prefetch.hoverDelay)},{passive:!0}),e.addEventListener("pointerleave",()=>{clearTimeout(t)},{passive:!0})}function Z(){let e=document.querySelectorAll("a[data-prefetch]");for(let t of e)dt(t)}function Ne(){!A.prefetch.enabled||K.initialized||(K.initialized=!0,Z(),document.addEventListener("router:loaded",Z),l("PREFETCH","ready"))}async function ft(e){if(e.defaultPrevented||e.button!==0||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;let t=e.target;if(!(t instanceof Element))return;let r=t.closest("a[href]");if(r instanceof HTMLAnchorElement){if(r.hasAttribute("data-confirm-logout")){if(e.preventDefault(),!await Se({title:"Déconnexion",message:"Êtes-vous sûr de vouloir vous déconnecter ?",confirmText:"Déconnexion"}))return;let o=await F(r.href,{method:"POST"});o?.type==="redirect"&&(window.location.href=o.redirect);return}z(r)||(e.preventDefault(),Ee(),T(r.href))}}async function pt(){document.body.classList.add("no-route-animation"),await T(location.href,{updateHistory:!1,force:!0}),requestAnimationFrame(()=>{document.body.classList.remove("no-route-animation")})}function De(){history.scrollRestoration="manual",document.addEventListener("click",ft),window.addEventListener("popstate",pt),be(),l("ROUTER","ready")}var je=!1,Y=null;function gt(){document.body.classList.add("is-routing")}function ht(){document.body.classList.remove("is-routing")}function bt(){clearTimeout(Y),Y=window.setTimeout(()=>{gt()},80),l("NAV_LOADING","start")}function X(){clearTimeout(Y),ht(),l("NAV_LOADING","end")}function ze(){je||(je=!0,document.addEventListener(ue,bt),document.addEventListener(me,X),document.addEventListener(de,X),document.addEventListener(fe,X),l("NAV_LOADING","initialized"))}var Fe="router-debug-panel",Ue=!1;function He(){return A.debug}function Et(){let e=document.createElement("div");return e.id=Fe,e.innerHTML=`
        <div class="router-debug-title">
            SPA DEBUG
        </div>

        <div class="router-debug-content">
        </div>
    `,document.body.appendChild(e),e}function yt(){return document.getElementById(Fe)||Et()}function At(e){if(!He())return;let r=yt().querySelector(".router-debug-content");if(!r)return;let i=document.createElement("div");for(i.textContent=`[${new Date().toLocaleTimeString()}] ${e}`,r.prepend(i);r.children.length>A.debugPanel.maxLogs;)r.lastChild?.remove()}function ke(){Ue||(Ue=!0,He()&&(["navigation:start","navigation:fetch","navigation:render","navigation:ready","navigation:error","navigation:abort"].forEach(e=>{document.addEventListener(e,t=>{At(`${e} → ${t.detail?.to||""}`)})}),l("DEBUG_PANEL","initialized")))}async function _e(e,t){try{return(await ye(e,{signal:t,headers:{Accept:"application/json"}}))?.data??{}}catch(r){throw r?.name==="AbortError"||t?.aborted?new DOMException("Search aborted","AbortError"):($("SEARCH_API",r),r instanceof j&&(r.silent=!0),r)}}function d(e){return String(e??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function St(e){return String(e??"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}function x(e){return String(e??"").trim().toLowerCase()}function g(e,t){let r=String(e??""),i=x(t);if(i==="")return d(r);let o=i.split(/\s+/).filter(Boolean).map(St);if(o.length===0)return d(r);let n=new RegExp(`(${o.join("|")})`,"ig");return r.split(n).map((a,s)=>{let c=d(a);return s%2===1?`<mark class="search-highlight">${c}</mark>`:c}).join("")}var $t=Object.freeze([{symbol:"一",title:"HSK1",description:"Débutant total",url:"chinois/grammaire/hsk1"},{symbol:"二",title:"HSK2",description:"Bases simples",url:"chinois/grammaire/hsk2"},{symbol:"三",title:"HSK3",description:"Intermédiaire débutant",url:"chinois/grammaire/hsk3"},{symbol:"四",title:"HSK4",description:"Intermédiaire solide",url:"chinois/grammaire/hsk4"}]);function Oe(e){let t=x(e).replaceAll(" ","");return t===""?[]:$t.filter(r=>[r.title,r.symbol].join(" ").toLowerCase().replaceAll(" ","").includes(t))}function Ge(e){e?.classList.add("has-results")}function Me(e){e?.classList.remove("has-results")}function H(e){e?.replaceChildren()}function h(e,t){let r=document.createElement("a");return r.href=e,r.className="search-result-item",r.innerHTML=t,r}function W(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.livre??"",a=e.thumbnail??"default",s=e.extension??"jpg",c=`${r}images/manga/thumbnail/${a}.${s}`,u=`${r}manga/series/${i}/${o}`;return h(u,`
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
        `)}function J(e,t){let r=e.id??"",i=e.type??"",o=e.titre??"",n=e.description??"",a=String(e.langue??"").toLowerCase(),s=String(e.niveau??"").toLowerCase(),c=i==="grammaire"?"📖":"📚",u=i==="grammaire"?s.toUpperCase():a==="jinyu"?"晋语":"中文",f=i==="grammaire"?`${t}chinois/grammaire/${s}/recherche/${r}`:`${t}chinois/vocabulaire/${a}/recherche/${r}`;return h(f,`
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
        `)}function ee(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/figurine/thumbnail/${s}.${c}`,f=`${r}figurine/waifus/${i}/${o}`;return h(f,`
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
        `)}function te(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/nendoroid/thumbnail/${s}.${c}`,f=`${r}nendoroid/waifus/${i}/${o}`;return h(f,`
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
        `)}function re(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/peluche/thumbnail/${s}.${c}`,f=`${r}peluche/waifus/${i}/${o}`;return h(f,`
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
        `)}function ie(e,t,r){let i=encodeURIComponent(e.slug??""),o=Number(e.numero??0),n=e.artbook??"",a=e.auteur??"",s=e.serie??"",c=e.thumbnail??"default",u=e.extension??"jpg",f=`${r}images/artbook/thumbnail/${c}.${u}`,b=`${r}manga/artbooks/${i}/${o}`,S=s||a||"Artbook";return h(b,`
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
                    ${g(S,t)}
                </small>

            </span>
        `)}function oe(e,t){let r=e.title??"",i=e.description??"",o=e.symbol??"→",n=e.url??"",a=`${t}${n}`;return h(a,`
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
        `)}function Be(e,t){let r=document.createElement("div");r.className="header-search-section-title",r.textContent=t,e.appendChild(r)}function v({title:e,results:t,buildItem:r,searchInput:i,searchResults:o,searchDropdown:n,setupResultItem:a,index:s}){return t.length===0||(Be(o,e),t.forEach(c=>{let u=r(c);a(u,s,i,o,n),o.appendChild(u),s++})),s}function Ke({mangas:e,artbooks:t,chinois:r,figurines:i,nendoroids:o,peluches:n,shortcuts:a,rawValue:s,basePath:c,searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,openDropdown:O,closeDropdown:G}){H(f);let p=0;if(p=v({title:"📚 SÉRIES",results:e.slice(0,5),buildItem:E=>W(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"📕 ARTBOOKS",results:t.slice(0,5),buildItem:E=>ie(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"⛩️ CHINOIS",results:r.slice(0,5),buildItem:E=>J(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:E=>ee(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"🪆 NENDOROIDS",results:o.slice(0,5),buildItem:E=>te(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"🧸 PELUCHES",results:n.slice(0,5),buildItem:E=>re(E,s,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p=v({title:"⚡ RACCOURCIS",results:a.slice(0,5),buildItem:E=>oe(E,c),searchInput:u,searchResults:f,searchDropdown:b,setupResultItem:S,index:p}),p===0){G(b);return}O(b)}function k(e,t){let r=D(".search-result-item",e);r.forEach(o=>{o.classList.remove("is-active")});let i=r[t];i&&(i.classList.add("is-active"),i.scrollIntoView({block:"nearest"}))}var w=0,I=null,Rt=200,_=null,L=null,y=-1;function Ve(){let e=C(".js-header-search"),t=C("#header-search-input"),r=C("#header-search-results"),i=C(".js-header-search-dropdown");!e||!t||!r||!i||e.dataset.initialized!=="true"&&(e.dataset.initialized="true",t.addEventListener("input",()=>{w++,L?.abort(),I=null,clearTimeout(_),_=setTimeout(()=>{qe(e,t,r,i)},Rt)}),e.addEventListener("submit",o=>{o.preventDefault(),clearTimeout(_),qe(e,t,r,i)}),t.addEventListener("keydown",o=>{vt(o,t,r,i)}),document.addEventListener("click",o=>{o.target.closest(".js-header-search")||N(t,r,i)}))}async function qe(e,t,r,i){let o=t.value,n=x(o);if(n===""){N(t,r,i);return}if(n===I)return;L?.abort(),I=n;let a=++w;L=new AbortController,y=-1;try{let s=e.dataset.basePath??"/",{mangas:c=[],artbooks:u=[],chinois:f=[],figurines:b=[],nendoroids:S=[],peluches:O=[]}=await _e(`${s}recherche?q=${encodeURIComponent(n)}`,L.signal),G=Oe(n);if(a!==w||t.value!==o)return;Ke({mangas:c,artbooks:u,chinois:f,figurines:b,nendoroids:S,peluches:O,shortcuts:G,rawValue:o,basePath:s,searchInput:t,searchResults:r,searchDropdown:i,setupResultItem:Tt,openDropdown:Pt,closeDropdown:Qe})}catch(s){if(a===w&&(I=null),s?.name==="AbortError")return}}function Tt(e,t,r,i,o){e.dataset.index=t,e.addEventListener("mouseenter",()=>{y=t,k(i,y)}),e.addEventListener("click",()=>{N(r,i,o)})}function vt(e,t,r,i){let o=D(".search-result-item",r);if(e.key==="ArrowDown"){if(!o.length)return;e.preventDefault(),y++,y>=o.length&&(y=0),k(r,y);return}if(e.key==="ArrowUp"){if(!o.length)return;e.preventDefault(),y--,y<0&&(y=o.length-1),k(r,y);return}if(e.key==="Enter"){let n=o[y];n&&(e.preventDefault(),N(t,r,i),T(n.href))}e.key==="Escape"&&N(t,r,i)}function Pt(e){e.classList.remove("is-loading"),Ge(e)}function Qe(e){Me(e),e.classList.remove("is-loading")}function N(e,t,r){clearTimeout(_),w++,I=null,L?.abort(),y=-1,e.value="",H(t),Qe(r)}var Ze=[["Router",De],["Prefetch",Ne],["Copy",Pe],["SearchController",Ve],["NavigationLoading",ze],["RouterDebugPanel",ke],["GlobalBackNavigation",Ie],["GlobalErrorHandlers",Re]];function m(e,t,r=null){let i,o=()=>i??=e().catch(a=>{throw i=void 0,a}),n=async()=>{let s=(await o())[t];if(typeof s!="function")throw new TypeError(`Initialiseur "${t}" introuvable.`);await s()};return n.preload=o,n.isRelevant=()=>r===null||document.querySelector(r)!==null,n}var Ct=m(()=>import("./chunks/ajouter-ELB5FXWM.js"),"initAjouterPage"),xt=m(()=>import("./chunks/modifier-Y6FDPJ3M.js"),"initModifierPage"),wt=m(()=>import("./chunks/update-note-NEQ2F7GI.js"),"initUpdateNote",".js-note-button"),It=m(()=>import("./chunks/delete-manga-NFDZLZGN.js"),"initDeleteManga",".js-delete-manga"),Lt=m(()=>import("./chunks/delete-artbook-OPIWNWHW.js"),"initDeleteArtbook",".js-delete-artbook"),Nt=m(()=>import("./chunks/update-read-status-E4YMEWCM.js"),"initUpdateReadStatus",".js-read-status-button"),Dt=m(()=>import("./chunks/ajouter-ZPDQXTOA.js"),"initAjouterPage"),jt=m(()=>import("./chunks/delete-figurine-MD7ST2YH.js"),"initDeleteFigurine",".js-delete-figurine"),zt=m(()=>import("./chunks/update-collect-status-LYRDEBXY.js"),"initUpdateCollectStatus",".js-figurine-collect-status-button"),Ut=m(()=>import("./chunks/ajouter-R35LNIYZ.js"),"initAjouterPage"),Ft=m(()=>import("./chunks/delete-peluche-YHD3GNRR.js"),"initDeletePeluche",".js-delete-peluche"),Ht=m(()=>import("./chunks/update-collect-status-47FV5UAT.js"),"initUpdatePelucheCollectStatus",".js-peluche-collect-status-button"),kt=m(()=>import("./chunks/ajouter-JEQEKJWD.js"),"initAjouterPage"),_t=m(()=>import("./chunks/delete-nendoroid-QQQUHLCE.js"),"initDeleteNendoroid",".js-delete-nendoroid"),Ot=m(()=>import("./chunks/update-collect-status-3DTX473B.js"),"initUpdateNendoroidCollectStatus",".js-nendoroid-collect-status-button"),Gt=m(()=>import("./chunks/ajouter-RCCNLDC5.js"),"initAjouterPage"),Mt=m(()=>import("./chunks/flashcards-vocabulaire-VPKI6HXD.js"),"initFlashcardsVocabulairePage"),Bt=m(()=>import("./chunks/flashcards-grammaire-IUFUX7EI.js"),"initFlashcardsGrammairePage"),Kt=m(()=>import("./chunks/toggle-grammar-mastery-ZGJNBFIN.js"),"initToggleGrammaireMaitrise",".grammar-ajax"),qt=m(()=>import("./chunks/toggle-vocabulary-mastery-CLHWHKSJ.js"),"initToggleVocabulaireMaitrise",".vocabulary-ajax"),Vt=m(()=>import("./chunks/delete-grammar-24C36A4K.js"),"initDeleteGrammaire",".grammaire-delete"),Qt=m(()=>import("./chunks/delete-vocabulary-L5K73NJA.js"),"initDeleteVocabulaire",".vocabulaire-delete"),Zt=m(()=>import("./chunks/profile-customization-YAWL2XNF.js"),"initProfileCustomization"),Xt=m(()=>import("./chunks/sql-FNMCKZH4.js"),"initSqlPage"),Xe=[{match:/^\/manga(?:\/|$)/,initializers:[["UpdateNote",wt],["DeleteManga",It],["DeleteArtbook",Lt],["UpdateReadStatus",Nt]]},{match:/^\/manga\/ajouter\/(manga|artbook)\/?$/,initializers:[["AjouterMangaPage",Ct]]},{match:/^\/manga\/series\/.+\/modifier\/\d+\/?$/,initializers:[["ModifierMangaPage",xt]]},{match:/^\/figurine(?:\/|$)/,initializers:[["DeleteFigurine",jt],["UpdateFigurineCollectStatus",zt]]},{match:/^\/figurine\/ajouter\/?$/,initializers:[["AjouterFigurinePage",Dt]]},{match:/^\/peluche(?:\/|$)/,initializers:[["DeletePeluche",Ft],["UpdatePelucheCollectStatus",Ht]]},{match:/^\/peluche\/ajouter\/?$/,initializers:[["AjouterPeluchePage",Ut]]},{match:/^\/nendoroid(?:\/|$)/,initializers:[["DeleteNendoroid",_t],["UpdateNendoroidCollectStatus",Ot]]},{match:/^\/nendoroid\/ajouter\/?$/,initializers:[["AjouterNendoroidPage",kt]]},{match:/^\/chinois(?:\/|$)/,initializers:[["ToggleGrammaireMaitrise",Kt],["ToggleVocabulaireMaitrise",qt],["DeleteGrammaire",Vt],["DeleteVocabulaire",Qt]]},{match:/^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,initializers:[["AjouterChinoisPage",Gt]]},{match:/^\/chinois\/flashcards\/vocabulaire\/?$/,initializers:[["FlashcardsVocabulaire",Mt]]},{match:/^\/chinois\/flashcards\/grammaire\/?$/,initializers:[["FlashcardsGrammaire",Bt]]},{match:/^\/profil\/personnalisation\/?$/,initializers:[["ProfileCustomization",Zt]]},{match:/^\/sql\/?$/,initializers:[["SqlPage",Xt]]}];function Ye(){window.setTimeout(()=>{location.reload()},300)}function We(){window.location.hostname.includes("localhost")&&(window.enableDebug=()=>{localStorage.setItem("lolissr_debug","1"),P("Debug activé","success"),Ye()},window.disableDebug=()=>{localStorage.removeItem("lolissr_debug"),P("Debug désactivé","success"),Ye()},window.__TEST_ERROR__=()=>{throw new Error("Test error")},window.__TEST_PROMISE_ERROR__=()=>{Promise.reject(new Error("Promise test error"))})}async function ne(e,t){le(e);try{await t(),l("INIT",`✅ ${e}`)}catch(r){$("INIT",r),R(r instanceof Error?r:new j(`Erreur pendant "${e}"`,{cause:r}))}finally{ce(e)}}async function Yt(){for(let[e,t]of Ze)await ne(e,t)}var Je=0;async function et(){let e=++Je,t=M(),r=Xe.filter(({match:i})=>i.test(t)).flatMap(({initializers:i})=>i);await $e(r,ne,()=>e===Je&&t===M())}async function tt(){l("APP","🚀 Boot"),We(),await Yt(),await et(),Ae(et),await ne("FlashToast",se),l("APP","✅ Ready")}function rt(){tt().catch(e=>{$("APP",e),R(e)})}function it(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",rt,{once:!0});return}rt()}it();
