import{a as ce,b as ue,c as me,d as de,e as fe,f as pe,g as Ee,h as ye,i as Se,j as ve,k as C}from"./chunks/chunk-MLOFOEWJ.js";import{a as Ae}from"./chunks/chunk-HXYIU4QO.js";import"./chunks/chunk-FZ7A3BZH.js";import{a as A}from"./chunks/chunk-NAIHXBIM.js";import{a as T,b as le,d as B,f as K,g as U,h as q,i as j,j as V,k as ge,l as he,n as be}from"./chunks/chunk-NHAQYG2U.js";import{a as z,b as H,c as $e}from"./chunks/chunk-7OQXZH5V.js";import{a as g,b as l,c as v,d as P,e as N,f as se}from"./chunks/chunk-DXQGVKAA.js";async function Ce(e,t,r=()=>!0){let i=e.filter(([,o])=>o.isRelevant?.()??!0),n=await Promise.allSettled(i.map(([,o])=>o.preload?.()));for(let o=0;o<i.length;o++){if(!r())return;let[a,s]=i[o];await t(a,()=>{if(n[o].status==="rejected")throw n[o].reason;return s()})}}function Re(){window.addEventListener("unhandledrejection",e=>{A(e.reason)}),window.addEventListener("error",e=>{A(e.error)}),l("ERROR_HANDLER","initialized")}async function Te(e){if(!e)return!1;try{return await navigator.clipboard.writeText(e),T("Copié !","success"),!0}catch{return T("Impossible de copier","error"),!1}}var Pe=!1;function xe(){Pe||(Pe=!0,se(document,"click","[data-copy]",async(e,t)=>{let r=t.dataset.copy;await Te(r)}))}var lt=`
input,
textarea,
select,
[contenteditable="true"]
`,ct=`
a,
button,
[role="button"]
`,Ie=!1,Q=!1;function we(e,t){return e instanceof Element&&!!e.closest(t)}function ut(e){return we(e,lt)}function mt(e){return we(e,ct)}function Le(){Q=!1}function dt(){Q=!0}function ft(){if(Q){l("BACKSPACE","blocked");return}if(dt(),l("BACKSPACE","navigate",location.pathname),window.history.length>1){window.history.back(),requestAnimationFrame(Le);return}C(g.baseUri).finally(Le)}function pt(e){e.key==="Backspace"&&(e.repeat||e.ctrlKey||e.metaKey||e.altKey||e.shiftKey||ut(e.target)||mt(e.target)||(e.preventDefault(),ft()))}function De(){if(Ie){l("BACKSPACE","already-init");return}Ie=!0,document.addEventListener("keydown",pt,{passive:!1}),l("BACKSPACE","ready")}var gt=3,Y=0;async function Ne(e){if(!g.prefetch.enabled||navigator.connection?.saveData===!0)return null;let t=K(e);if(t===K(location.href)||V.has(t))return null;let r=ge(t);if(r)return l("PREFETCH","cache-hit",t),r;let i=be(t);if(i)return l("PREFETCH","reuse",t),i;if(l("PREFETCH","fetch",t),Y>=gt)return null;Y++;let n=new AbortController,o;return o=(async()=>{try{let a=await H(t,{timeout:g.prefetch.timeout,headers:{"X-Page-Format":"fragment",Accept:"application/json","X-Prefetch":"true","Cache-Control":"no-cache"},signal:n.signal});return a?.type!=="page"?(l("PREFETCH","invalid-response",t),null):n.signal.aborted||V.has(t)?(l("PREFETCH","skip-invalidated",t),null):(he(t,a),l("PREFETCH","success",t),a)}catch(a){return a?.name==="AbortError"?(l("PREFETCH","aborted",t),null):(v("PREFETCH",a),null)}finally{Y--,j.get(t)?.promise===o&&j.delete(t)}})(),j.set(t,{promise:o,controller:n}),o}function ht(e){if(!(e instanceof HTMLAnchorElement)||U(e)||e.hasAttribute("data-confirm-logout")||e.pathname.endsWith("/deconnexion")||e.dataset.prefetchBound==="true")return;e.dataset.prefetchBound="true";let t=null;e.addEventListener("pointerenter",()=>{clearTimeout(t),t=window.setTimeout(()=>{Ne(e.href)},g.prefetch.hoverDelay)},{passive:!0}),e.addEventListener("pointerleave",()=>{clearTimeout(t)},{passive:!0})}function Z(){let e=document.querySelectorAll("a[data-prefetch]");for(let t of e)ht(t)}function ze(){!g.prefetch.enabled||q.initialized||(q.initialized=!0,Z(),document.addEventListener("router:loaded",Z),l("PREFETCH","ready"))}async function bt(e){if(e.defaultPrevented||e.button!==0||e.ctrlKey||e.metaKey||e.shiftKey||e.altKey)return;let t=e.target;if(!(t instanceof Element))return;let r=t.closest("a[href]");if(r instanceof HTMLAnchorElement){if(r.hasAttribute("data-confirm-logout")){if(e.preventDefault(),!await Ae({title:"Déconnexion",message:"Êtes-vous sûr de vouloir vous déconnecter ?",confirmText:"Déconnexion"}))return;let n=await H(r.href,{method:"POST"});n?.type==="redirect"&&(window.location.href=n.redirect);return}U(r)||(e.preventDefault(),ye(),C(r.href))}}async function Et(){document.body.classList.add("no-route-animation"),await C(location.href,{updateHistory:!1,force:!0}),requestAnimationFrame(()=>{document.body.classList.remove("no-route-animation")})}function yt(e){e.target instanceof Element&&e.target.closest("header .nav-link-icon, header .site-profile-link")&&e.preventDefault()}function Ue(){Se(),history.scrollRestoration="manual",document.addEventListener("click",bt),document.addEventListener("dragstart",yt),window.addEventListener("popstate",Et),Ee(),l("ROUTER","ready")}var je=!1,W=null;function St(){document.body.classList.add("is-routing")}function $t(){document.body.classList.remove("is-routing")}function vt(){clearTimeout(W),W=window.setTimeout(()=>{St()},80),l("NAV_LOADING","start")}function X(){clearTimeout(W),$t(),l("NAV_LOADING","end")}function He(){je||(je=!0,document.addEventListener(me,vt),document.addEventListener(de,X),document.addEventListener(fe,X),document.addEventListener(pe,X),l("NAV_LOADING","initialized"))}var Fe="router-debug-panel",ke=!1;function _e(){return g.debug}function At(){let e=document.createElement("div");return e.id=Fe,e.innerHTML=`
        <div class="router-debug-title">
            DÉBOGAGE SPA
        </div>

        <div class="router-debug-content">
        </div>
    `,document.body.appendChild(e),e}function Ct(){return document.getElementById(Fe)||At()}function Rt(e){if(!_e())return;let r=Ct().querySelector(".router-debug-content");if(!r)return;let i=document.createElement("div");for(i.textContent=`[${new Date().toLocaleTimeString()}] ${e}`,r.prepend(i);r.children.length>g.debugPanel.maxLogs;)r.lastChild?.remove()}function Ge(){ke||(ke=!0,_e()&&(["navigation:start","navigation:fetch","navigation:render","navigation:ready","navigation:error","navigation:abort"].forEach(e=>{document.addEventListener(e,t=>{Rt(`${e} → ${t.detail?.to||""}`)})}),l("DEBUG_PANEL","initialized")))}async function Oe(e,t){try{return(await $e(e,{signal:t,headers:{Accept:"application/json"}}))?.data??{}}catch(r){throw r?.name==="AbortError"||t?.aborted?new DOMException("Search aborted","AbortError"):(v("SEARCH_API",r),r instanceof z&&(r.silent=!0),r)}}function d(e){return String(e??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function Tt(e){return String(e??"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}function x(e){return String(e??"").trim().toLowerCase()}function h(e,t){let r=String(e??""),i=x(t);if(i==="")return d(r);let n=i.split(/\s+/).filter(Boolean).map(Tt);if(n.length===0)return d(r);let o=new RegExp(`(${n.join("|")})`,"ig");return r.split(o).map((a,s)=>{let c=d(a);return s%2===1?`<mark class="search-highlight">${c}</mark>`:c}).join("")}var Pt=Object.freeze([{symbol:"一",title:"HSK1",description:"Débutant total",url:"chinois/grammaire/hsk1"},{symbol:"二",title:"HSK2",description:"Bases simples",url:"chinois/grammaire/hsk2"},{symbol:"三",title:"HSK3",description:"Intermédiaire débutant",url:"chinois/grammaire/hsk3"},{symbol:"四",title:"HSK4",description:"Intermédiaire solide",url:"chinois/grammaire/hsk4"}]);function Me(e){let t=x(e).replaceAll(" ","");return t===""?[]:Pt.filter(r=>[r.title,r.symbol].join(" ").toLowerCase().replaceAll(" ","").includes(t))}function Be(e){e?.classList.add("has-results")}function Ke(e){e?.classList.remove("has-results")}function k(e){e?.replaceChildren()}function b(e,t){let r=document.createElement("a");return r.href=e,r.className="search-result-item",r.innerHTML=t,r}function J(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.livre??"",a=e.thumbnail??"default",s=e.extension??"jpg",c=`${r}images/manga/thumbnail/${a}.${s}`,u=`${r}manga/series/${i}/${n}`;return b(u,`
            <img
                src="${c}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${h(o,t)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(n).padStart(2,"0")}
                </small>

            </span>
        `)}function ee(e,t){let r=e.id??"",i=e.type??"",n=e.titre??"",o=e.description??"",a=String(e.langue??"").toLowerCase(),s=String(e.niveau??"").toLowerCase(),c=i==="grammaire"?"📖":"📚",u=i==="grammaire"?s.toUpperCase():a==="jinyu"?"晋语":"中文",f=i==="grammaire"?`${t}chinois/grammaire/${s}/recherche/${r}`:`${t}chinois/vocabulaire/${a}/recherche/${r}`;return b(f,`
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
        `)}function te(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/figurine/thumbnail/${s}.${c}`,f=`${r}figurine/figurines/${i}/${n}`;return b(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${h(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${h(a,t)}
                </small>

            </span>
        `)}function re(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/nendoroid/thumbnail/${s}.${c}`,f=`${r}nendoroid/nendoroids/${i}/${n}`;return b(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${h(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${h(a,t)}
                </small>

            </span>
        `)}function ie(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.waifu??"",a=e.origin??"",s=e.thumbnail??"default",c=e.extension??"jpg",u=`${r}images/peluche/thumbnail/${s}.${c}`,f=`${r}peluche/peluches/${i}/${n}`;return b(f,`
            <img
                src="${u}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${h(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${h(a,t)}
                </small>

            </span>
        `)}function ne(e,t,r){let i=encodeURIComponent(e.slug??""),n=Number(e.numero??0),o=e.artbook??"",a=e.auteur??"",s=e.serie??"",c=e.thumbnail??"default",u=e.extension??"jpg",f=`${r}images/artbook/thumbnail/${c}.${u}`,E=`${r}manga/artbooks/${i}/${n}`,$=s||a||"Artbook";return b(E,`
            <img
                src="${f}"
                alt="${d(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${h(o,t)}
                </strong>

                <small class="search-result-meta">
                    ${h($,t)}
                </small>

            </span>
        `)}function oe(e,t){let r=e.title??"",i=e.description??"",n=e.symbol??"→",o=e.url??"",a=`${t}${o}`;return b(a,`
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
        `)}function qe(e,t){let r=document.createElement("div");r.className="header-search-section-title",r.textContent=t,e.appendChild(r)}function R({title:e,results:t,buildItem:r,searchInput:i,searchResults:n,searchDropdown:o,setupResultItem:a,index:s}){return t.length===0||(qe(n,e),t.forEach(c=>{let u=r(c);a(u,s,i,n,o),n.appendChild(u),s++})),s}function Ve({mangas:e,artbooks:t,chinois:r,figurines:i,nendoroids:n,peluches:o,shortcuts:a,rawValue:s,basePath:c,searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,openDropdown:O,closeDropdown:M}){k(f);let p=0;if(p=R({title:"📚 SÉRIES",results:e.slice(0,5),buildItem:y=>J(y,s,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"📕 ARTBOOKS",results:t.slice(0,5),buildItem:y=>ne(y,s,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"⛩️ CHINOIS",results:r.slice(0,5),buildItem:y=>ee(y,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:y=>te(y,s,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"🪆 NENDOROIDS",results:n.slice(0,5),buildItem:y=>re(y,s,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"🧸 PELUCHES",results:o.slice(0,5),buildItem:y=>ie(y,s,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p=R({title:"⚡ RACCOURCIS",results:a.slice(0,5),buildItem:y=>oe(y,c),searchInput:u,searchResults:f,searchDropdown:E,setupResultItem:$,index:p}),p===0){M(E);return}O(E)}function F(e,t){let r=N(".search-result-item",e);r.forEach(n=>{n.classList.remove("is-active")});let i=r[t];i&&(i.classList.add("is-active"),i.scrollIntoView({block:"nearest"}))}var I=0,L=null,xt=200,_=null,w=null,S=-1;function Ye(){let e=P(".js-header-search"),t=P("#header-search-input"),r=P("#header-search-results"),i=P(".js-header-search-dropdown");!e||!t||!r||!i||e.dataset.initialized!=="true"&&(e.dataset.initialized="true",t.addEventListener("input",()=>{I++,w?.abort(),L=null,clearTimeout(_),_=setTimeout(()=>{Qe(e,t,r,i)},xt)}),e.addEventListener("submit",n=>{n.preventDefault(),clearTimeout(_),Qe(e,t,r,i)}),t.addEventListener("keydown",n=>{Lt(n,t,r,i)}),document.addEventListener("click",n=>{n.target.closest(".js-header-search")||D(t,r,i)}))}async function Qe(e,t,r,i){let n=t.value,o=x(n);if(o===""){D(t,r,i);return}if(o===L)return;w?.abort(),L=o;let a=++I;w=new AbortController,S=-1;try{let s=e.dataset.basePath??"/",{mangas:c=[],artbooks:u=[],chinois:f=[],figurines:E=[],nendoroids:$=[],peluches:O=[]}=await Oe(`${s}recherche?q=${encodeURIComponent(o)}`,w.signal),M=Me(o);if(a!==I||t.value!==n)return;Ve({mangas:c,artbooks:u,chinois:f,figurines:E,nendoroids:$,peluches:O,shortcuts:M,rawValue:n,basePath:s,searchInput:t,searchResults:r,searchDropdown:i,setupResultItem:It,openDropdown:wt,closeDropdown:Ze})}catch(s){if(a===I&&(L=null),s?.name==="AbortError")return}}function It(e,t,r,i,n){e.dataset.index=t,e.addEventListener("mouseenter",()=>{S=t,F(i,S)}),e.addEventListener("click",()=>{D(r,i,n)})}function Lt(e,t,r,i){let n=N(".search-result-item",r);if(e.key==="ArrowDown"){if(!n.length)return;e.preventDefault(),S++,S>=n.length&&(S=0),F(r,S);return}if(e.key==="ArrowUp"){if(!n.length)return;e.preventDefault(),S--,S<0&&(S=n.length-1),F(r,S);return}if(e.key==="Enter"){let o=n[S];o&&(e.preventDefault(),D(t,r,i),C(o.href))}e.key==="Escape"&&D(t,r,i)}function wt(e){e.classList.remove("is-loading"),Be(e)}function Ze(e){Ke(e),e.classList.remove("is-loading")}function D(e,t,r){clearTimeout(_),I++,L=null,w?.abort(),S=-1,e.value="",k(t),Ze(r)}var Xe=[["Router",Ue],["Prefetch",ze],["Copy",xe],["GlobalSearchController",Ye],["NavigationLoading",He],["RouterDebugPanel",Ge],["GlobalBackNavigation",De],["GlobalErrorHandlers",Re]];function m(e,t,r=null){let i,n=()=>i??=e().catch(a=>{throw i=void 0,a}),o=async()=>{let s=(await n())[t];if(typeof s!="function")throw new TypeError(`Initialiseur "${t}" introuvable.`);await s()};return o.preload=n,o.isRelevant=()=>r===null||document.querySelector(r)!==null,o}var Dt=m(()=>import("./chunks/create-37LXHUNH.js"),"initCreatePage"),Nt=m(()=>import("./chunks/edit-5YOCCLG7.js"),"initEditPage"),zt=m(()=>import("./chunks/update-note-EM46UFYJ.js"),"initUpdateNote",".js-note-button"),Ut=m(()=>import("./chunks/delete-manga-6ATGZXRZ.js"),"initDeleteManga",".js-delete-manga"),jt=m(()=>import("./chunks/delete-artbook-K7XW666U.js"),"initDeleteArtbook",".js-delete-artbook"),Ht=m(()=>import("./chunks/update-read-status-JGUBKCN4.js"),"initUpdateReadStatus",".js-read-status-button"),kt=m(()=>import("./chunks/create-YJYH7UUL.js"),"initCreatePage"),Ft=m(()=>import("./chunks/delete-figurine-ZBAQXU4M.js"),"initDeleteFigurine",".js-delete-figurine"),_t=m(()=>import("./chunks/update-collect-status-LQKIDBSN.js"),"initUpdateCollectStatus",".js-figurine-collect-status-button"),Gt=m(()=>import("./chunks/create-R2GRCJ2G.js"),"initCreatePage"),Ot=m(()=>import("./chunks/delete-peluche-FT574UAK.js"),"initDeletePeluche",".js-delete-peluche"),Mt=m(()=>import("./chunks/update-collect-status-J7HC72IQ.js"),"initUpdatePelucheCollectStatus",".js-peluche-collect-status-button"),Bt=m(()=>import("./chunks/create-227XRNEA.js"),"initCreatePage"),Kt=m(()=>import("./chunks/delete-nendoroid-2UGHA2WL.js"),"initDeleteNendoroid",".js-delete-nendoroid"),qt=m(()=>import("./chunks/update-collect-status-VRRSN3PI.js"),"initUpdateNendoroidCollectStatus",".js-nendoroid-collect-status-button"),Vt=m(()=>import("./chunks/create-2VRQK7AX.js"),"initCreatePage"),Qt=m(()=>import("./chunks/flashcards-vocabulaire-JJVY4UTX.js"),"initFlashcardsVocabulairePage"),Yt=m(()=>import("./chunks/flashcards-grammaire-SQH2QSS7.js"),"initFlashcardsGrammairePage"),Zt=m(()=>import("./chunks/toggle-grammar-mastery-4RDN7UR2.js"),"initToggleGrammaireMaitrise",".grammar-ajax"),Xt=m(()=>import("./chunks/toggle-vocabulary-mastery-I2GDPQUE.js"),"initToggleVocabulaireMaitrise",".vocabulary-ajax"),Wt=m(()=>import("./chunks/delete-grammar-4KZX6VOO.js"),"initDeleteGrammaire",".grammaire-delete"),Jt=m(()=>import("./chunks/delete-vocabulary-GQPMWSAK.js"),"initDeleteVocabulaire",".vocabulaire-delete"),er=m(()=>import("./chunks/profile-customization-TZVP5BN3.js"),"initProfileCustomization"),tr=m(()=>import("./chunks/sql-ZYHKQICQ.js"),"initSqlPage"),We=[{match:/^\/manga(?:\/|$)/,initializers:[["UpdateNote",zt],["DeleteManga",Ut],["DeleteArtbook",jt],["UpdateReadStatus",Ht]]},{match:/^\/manga\/ajouter\/(manga|artbook)\/?$/,initializers:[["AjouterMangaPage",Dt]]},{match:/^\/manga\/series\/.+\/modifier\/\d+\/?$/,initializers:[["ModifierMangaPage",Nt]]},{match:/^\/figurine(?:\/|$)/,initializers:[["DeleteFigurine",Ft],["UpdateFigurineCollectStatus",_t]]},{match:/^\/figurine\/ajouter\/?$/,initializers:[["AjouterFigurinePage",kt]]},{match:/^\/peluche(?:\/|$)/,initializers:[["DeletePeluche",Ot],["UpdatePelucheCollectStatus",Mt]]},{match:/^\/peluche\/ajouter\/?$/,initializers:[["AjouterPeluchePage",Gt]]},{match:/^\/nendoroid(?:\/|$)/,initializers:[["DeleteNendoroid",Kt],["UpdateNendoroidCollectStatus",qt]]},{match:/^\/nendoroid\/ajouter\/?$/,initializers:[["AjouterNendoroidPage",Bt]]},{match:/^\/chinois(?:\/|$)/,initializers:[["ToggleGrammaireMaitrise",Zt],["ToggleVocabulaireMaitrise",Xt],["DeleteGrammaire",Wt],["DeleteVocabulaire",Jt]]},{match:/^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,initializers:[["AjouterChinoisPage",Vt]]},{match:/^\/chinois\/flashcards\/vocabulaire\/?$/,initializers:[["FlashcardsVocabulaire",Qt]]},{match:/^\/chinois\/flashcards\/grammaire\/?$/,initializers:[["FlashcardsGrammaire",Yt]]},{match:/^\/profil\/personnalisation\/?$/,initializers:[["ProfileCustomization",er]]},{match:/^\/sql\/?$/,initializers:[["SqlPage",tr]]}];var Je="lolissr_debug";function et(){localStorage.setItem(Je,"1")}function tt(){localStorage.removeItem(Je)}function rt(){window.setTimeout(()=>{location.reload()},300)}function it(){g.isLocalhost&&(window.enableDebug=()=>{et(),T("Debug activé","success"),rt()},window.disableDebug=()=>{tt(),T("Debug désactivé","success"),rt()},window.__TEST_ERROR__=()=>{throw new Error("Test error")},window.__TEST_PROMISE_ERROR__=()=>{Promise.reject(new Error("Promise test error"))})}async function ae(e,t){ce(e);try{await t(),l("INIT",`✅ ${e}`)}catch(r){v("INIT",r),A(r instanceof Error?r:new z(`Erreur pendant "${e}"`,{cause:r}))}finally{ue(e)}}async function rr(){for(let[e,t]of Xe)await ae(e,t)}var G=0;async function nt(){let e=++G,t=B(),r=We.filter(({match:i})=>i.test(t)).flatMap(({initializers:i})=>i);await Ce(r,ae,()=>e===G&&t===B())}async function ot(){l("APP","🚀 Boot"),it();let e=G;ve(nt),await rr(),G===e&&await nt(),await ae("FlashToast",le),l("APP","✅ Ready")}function at(){ot().catch(e=>{v("APP",e),A(e)})}function st(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",at,{once:!0});return}at()}st();
