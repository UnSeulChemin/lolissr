import{a as C}from"./chunks/chunk-TCJWMAZW.js";import{a as ct,b as ut,c as mt,d as dt,e as pt,f as ft,g as Et,h as yt,i as St,j as $t,k as R}from"./chunks/chunk-XIF4Z3TT.js";import{a as Ct}from"./chunks/chunk-HXYIU4QO.js";import"./chunks/chunk-AO7XTYFX.js";import{b as B,e as q,f as z,g as V,h as D,i as Q,j as gt,k as ht,m as bt}from"./chunks/chunk-WO4MMDVC.js";import{a as U,b as j,c as At}from"./chunks/chunk-IR5A7MF5.js";import{a as K,b as lt}from"./chunks/chunk-3HMTKJH2.js";import{a as v,b as w,c as st}from"./chunks/chunk-BFFYOOEW.js";import{a as y,b as l,c as $}from"./chunks/chunk-UIBCKW3Z.js";async function Rt(t,e,r=()=>!0){let i=t.filter(([,o])=>o.isRelevant?.()??!0),n=await Promise.allSettled(i.map(([,o])=>o.preload?.()));for(let o=0;o<i.length;o++){if(!r())return;let[a,s]=i[o];await e(a,()=>{if(n[o].status==="rejected")throw n[o].reason;return s()})}}function Tt(){window.addEventListener("unhandledrejection",t=>{C(t.reason)}),window.addEventListener("error",t=>{C(t.error)}),l("ERROR_HANDLER","initialized")}async function vt(t){if(!t)return!1;try{return await navigator.clipboard.writeText(t),K("Copié !","success"),!0}catch{return K("Impossible de copier","error"),!1}}var Pt=!1;function xt(){Pt||(Pt=!0,st(document,"click","[data-copy]",async(t,e)=>{let r=e.dataset.copy;await vt(r)}))}var Jt=`
input,
textarea,
select,
[contenteditable="true"]
`,te=`
a,
button,
[role="button"]
`,It=!1,Z=!1;function Nt(t,e){return t instanceof Element&&!!t.closest(e)}function ee(t){return Nt(t,Jt)}function re(t){return Nt(t,te)}function Lt(){Z=!1}function ie(){Z=!0}function ne(){if(Z){l("BACKSPACE","blocked");return}if(ie(),l("BACKSPACE","navigate",location.pathname),window.history.length>1){window.history.back(),requestAnimationFrame(Lt);return}R(y.baseUri).finally(Lt)}function oe(t){t.key==="Backspace"&&(t.repeat||t.ctrlKey||t.metaKey||t.altKey||t.shiftKey||ee(t.target)||re(t.target)||(t.preventDefault(),ne()))}function wt(){if(It){l("BACKSPACE","already-init");return}It=!0,document.addEventListener("keydown",oe,{passive:!1}),l("BACKSPACE","ready")}var ae=3,X=0;async function Ut(t){if(!y.prefetch.enabled||navigator.connection?.saveData===!0)return null;let e=q(t);if(e===q(location.href)||Q.has(e))return null;let r=gt(e);if(r)return l("PREFETCH","cache-hit",e),r;let i=bt(e);if(i)return l("PREFETCH","reuse",e),i;if(l("PREFETCH","fetch",e),X>=ae)return null;X++;let n=new AbortController,o;return o=(async()=>{try{let a=await j(e,{timeout:y.prefetch.timeout,headers:{"X-Page-Format":"fragment",Accept:"application/json","X-Prefetch":"true","Cache-Control":"no-cache"},signal:n.signal});return a?.type!=="page"?(l("PREFETCH","invalid-response",e),null):n.signal.aborted||Q.has(e)?(l("PREFETCH","skip-invalidated",e),null):(ht(e,a),l("PREFETCH","success",e),a)}catch(a){return a?.name==="AbortError"?(l("PREFETCH","aborted",e),null):($("PREFETCH",a),null)}finally{X--,D.get(e)?.promise===o&&D.delete(e)}})(),D.set(e,{promise:o,controller:n}),o}function se(t){if(!(t instanceof HTMLAnchorElement)||z(t)||t.hasAttribute("data-confirm-logout")||t.pathname.endsWith("/deconnexion")||t.dataset.prefetchBound==="true")return;t.dataset.prefetchBound="true";let e=null;t.addEventListener("pointerenter",()=>{clearTimeout(e),e=window.setTimeout(()=>{Ut(t.href)},y.prefetch.hoverDelay)},{passive:!0}),t.addEventListener("pointerleave",()=>{clearTimeout(e)},{passive:!0})}function Y(){let t=document.querySelectorAll("a[data-prefetch]");for(let e of t)se(e)}function zt(){!y.prefetch.enabled||V.initialized||(V.initialized=!0,Y(),document.addEventListener("router:loaded",Y),l("PREFETCH","ready"))}async function le(t){if(t.defaultPrevented||t.button!==0||t.ctrlKey||t.metaKey||t.shiftKey||t.altKey)return;let e=t.target;if(!(e instanceof Element))return;let r=e.closest("a[href]");if(r instanceof HTMLAnchorElement){if(r.hasAttribute("data-confirm-logout")){if(t.preventDefault(),!await Ct({title:"Déconnexion",message:"Êtes-vous sûr de vouloir vous déconnecter ?",confirmText:"Déconnexion"}))return;let n=await j(r.href,{method:"POST"});n?.type==="redirect"&&(window.location.href=n.redirect);return}z(r)||(t.preventDefault(),yt(),R(r.href))}}async function ce(){document.body.classList.add("no-route-animation"),await R(location.href,{updateHistory:!1,force:!0}),requestAnimationFrame(()=>{document.body.classList.remove("no-route-animation")})}function ue(t){t.target instanceof Element&&t.target.closest("header .nav-link-icon, header .site-profile-link")&&t.preventDefault()}function Dt(){St(),history.scrollRestoration="manual",document.addEventListener("click",le),document.addEventListener("dragstart",ue),window.addEventListener("popstate",ce),Et(),l("ROUTER","ready")}var jt=!1,J=null;function me(){document.body.classList.add("is-routing")}function de(){document.body.classList.remove("is-routing")}function pe(){clearTimeout(J),J=window.setTimeout(()=>{me()},80),l("NAV_LOADING","start")}function W(){clearTimeout(J),de(),l("NAV_LOADING","end")}function Ft(){jt||(jt=!0,document.addEventListener(mt,pe),document.addEventListener(dt,W),document.addEventListener(pt,W),document.addEventListener(ft,W),l("NAV_LOADING","initialized"))}async function Ht(t,e){try{return(await At(t,{signal:e,headers:{Accept:"application/json"}}))?.data??{}}catch(r){throw r?.name==="AbortError"||e?.aborted?new DOMException("Search aborted","AbortError"):($("SEARCH_API",r),r instanceof U&&(r.silent=!0),r)}}function c(t){return String(t??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function fe(t){return String(t??"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&")}function P(t){return String(t??"").trim().toLowerCase()}function g(t,e){let r=String(t??""),i=P(e);if(i==="")return c(r);let n=i.split(/\s+/).filter(Boolean).map(fe);if(n.length===0)return c(r);let o=new RegExp(`(${n.join("|")})`,"ig");return r.split(o).map((a,s)=>{let u=c(a);return s%2===1?`<mark class="search-highlight">${u}</mark>`:u}).join("")}var ge=Object.freeze([{symbol:"一",title:"HSK1",description:"Débutant total",url:"chinois/grammaire/hsk1"},{symbol:"二",title:"HSK2",description:"Bases simples",url:"chinois/grammaire/hsk2"},{symbol:"三",title:"HSK3",description:"Intermédiaire débutant",url:"chinois/grammaire/hsk3"},{symbol:"四",title:"HSK4",description:"Intermédiaire solide",url:"chinois/grammaire/hsk4"}]);function kt(t){let e=P(t).replaceAll(" ","");return e===""?[]:ge.filter(r=>[r.title,r.symbol].join(" ").toLowerCase().replaceAll(" ","").includes(e))}function Gt(t){t?.classList.add("has-results")}function Ot(t){t?.classList.remove("has-results")}function F(t){t?.replaceChildren()}function h(t,e){let r=document.createElement("a");return r.href=t,r.className="search-result-item",r.innerHTML=e,r}function tt(t,e,r){let i=encodeURIComponent(t.slug??""),n=Number(t.numero??0),o=t.livre??"",a=t.thumbnail??"default",s=t.extension??"jpg",u=t.thumbnailUrl??`${r}images/manga/thumbnail/${a}.${s}`,d=`${r}manga/series/${i}/${n}`;return h(d,`
            <img
                src="${c(u)}"
                alt="${c(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,e)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(n).padStart(2,"0")}
                </small>

            </span>
        `)}function et(t,e){let r=t.id??"",i=t.type??"",n=t.titre??"",o=t.description??"",a=String(t.langue??"").toLowerCase(),s=String(t.niveau??"").toLowerCase(),u=i==="grammaire"?"📖":"📚",d=i==="grammaire"?s.toUpperCase():a==="jinyu"?"晋语":"中文",p=i==="grammaire"?`${e}chinois/grammaire/${s}/recherche/${r}`:`${e}chinois/vocabulaire/${a}/recherche/${r}`;return h(p,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${c(u)}
                </span>

                <span class="search-result-category-label">
                    ${c(d)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${c(n)}
                </strong>

                <small class="search-result-meta">
                    ${c(o)}
                </small>

            </span>
        `)}function rt(t,e,r){let i=encodeURIComponent(t.slug??""),n=Number(t.numero??0),o=t.waifu??"",a=t.origin??"",s=t.thumbnail??"default",u=t.extension??"jpg",d=t.thumbnailUrl??`${r}images/figurine/thumbnail/${s}.${u}`,p=`${r}figurine/figurines/${i}/${n}`;return h(p,`
            <img
                src="${c(d)}"
                alt="${c(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,e)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,e)}
                </small>

            </span>
        `)}function it(t,e,r){let i=encodeURIComponent(t.slug??""),n=Number(t.numero??0),o=t.waifu??"",a=t.origin??"",s=t.thumbnail??"default",u=t.extension??"jpg",d=t.thumbnailUrl??`${r}images/nendoroid/thumbnail/${s}.${u}`,p=`${r}nendoroid/nendoroids/${i}/${n}`;return h(p,`
            <img
                src="${c(d)}"
                alt="${c(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,e)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,e)}
                </small>

            </span>
        `)}function nt(t,e,r){let i=encodeURIComponent(t.slug??""),n=Number(t.numero??0),o=t.waifu??"",a=t.origin??"",s=t.thumbnail??"default",u=t.extension??"jpg",d=t.thumbnailUrl??`${r}images/peluche/thumbnail/${s}.${u}`,p=`${r}peluche/peluches/${i}/${n}`;return h(p,`
            <img
                src="${c(d)}"
                alt="${c(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,e)}
                </strong>

                <small class="search-result-meta">
                    ${g(a,e)}
                </small>

            </span>
        `)}function ot(t,e,r){let i=encodeURIComponent(t.slug??""),n=Number(t.numero??0),o=t.artbook??"",a=t.auteur??"",s=t.serie??"",u=t.thumbnail??"default",d=t.extension??"jpg",p=t.thumbnailUrl??`${r}images/artbook/thumbnail/${u}.${d}`,b=`${r}manga/artbooks/${i}/${n}`,A=s||a||"Artbook";return h(b,`
            <img
                src="${c(p)}"
                alt="${c(o)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(o,e)}
                </strong>

                <small class="search-result-meta">
                    ${g(A,e)}
                </small>

            </span>
        `)}function at(t,e){let r=t.title??"",i=t.description??"",n=t.symbol??"→",o=t.url??"",a=`${e}${o}`;return h(a,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${c(n)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${c(r)}
                </strong>

                <small class="search-result-meta">
                    ${c(i)}
                </small>

            </span>
        `)}function Mt(t,e){let r=document.createElement("div");r.className="header-search-section-title",r.textContent=e,t.appendChild(r)}function T({title:t,results:e,buildItem:r,searchInput:i,searchResults:n,searchDropdown:o,setupResultItem:a,index:s}){return e.length===0||(Mt(n,t),e.forEach(u=>{let d=r(u);a(d,s,i,n,o),n.appendChild(d),s++})),s}function _t({mangas:t,artbooks:e,chinois:r,figurines:i,nendoroids:n,peluches:o,shortcuts:a,rawValue:s,basePath:u,searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,openDropdown:M,closeDropdown:_}){F(p);let f=0;if(f=T({title:"📚 SÉRIES",results:t.slice(0,5),buildItem:E=>tt(E,s,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"📕 ARTBOOKS",results:e.slice(0,5),buildItem:E=>ot(E,s,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"⛩️ CHINOIS",results:r.slice(0,5),buildItem:E=>et(E,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:E=>rt(E,s,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"🪆 NENDOROIDS",results:n.slice(0,5),buildItem:E=>it(E,s,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"🧸 PELUCHES",results:o.slice(0,5),buildItem:E=>nt(E,s,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f=T({title:"⚡ RACCOURCIS",results:a.slice(0,5),buildItem:E=>at(E,u),searchInput:d,searchResults:p,searchDropdown:b,setupResultItem:A,index:f}),f===0){_(b);return}M(b)}function H(t,e){let r=w(".search-result-item",t);r.forEach(n=>{n.classList.remove("is-active")});let i=r[e];i&&(i.classList.add("is-active"),i.scrollIntoView({block:"nearest"}))}var x=0,I=null,he=200,k=null,L=null,S=-1;function Bt(){let t=v(".js-header-search"),e=v("#header-search-input"),r=v("#header-search-results"),i=v(".js-header-search-dropdown");!t||!e||!r||!i||t.dataset.initialized!=="true"&&(t.dataset.initialized="true",e.addEventListener("input",()=>{x++,L?.abort(),I=null,clearTimeout(k),k=setTimeout(()=>{Kt(t,e,r,i)},he)}),t.addEventListener("submit",n=>{n.preventDefault(),clearTimeout(k),Kt(t,e,r,i)}),e.addEventListener("keydown",n=>{Ee(n,e,r,i)}),document.addEventListener("click",n=>{n.target.closest(".js-header-search")||N(e,r,i)}))}async function Kt(t,e,r,i){let n=e.value,o=P(n);if(o===""){N(e,r,i);return}if(o===I)return;L?.abort(),I=o;let a=++x;L=new AbortController,S=-1;try{let s=t.dataset.basePath??"/",{mangas:u=[],artbooks:d=[],chinois:p=[],figurines:b=[],nendoroids:A=[],peluches:M=[]}=await Ht(`${s}recherche?q=${encodeURIComponent(o)}`,L.signal),_=kt(o);if(a!==x||e.value!==n)return;_t({mangas:u,artbooks:d,chinois:p,figurines:b,nendoroids:A,peluches:M,shortcuts:_,rawValue:n,basePath:s,searchInput:e,searchResults:r,searchDropdown:i,setupResultItem:be,openDropdown:ye,closeDropdown:qt})}catch(s){if(a===x&&(I=null),s?.name==="AbortError")return}}function be(t,e,r,i,n){t.dataset.index=e,t.addEventListener("mouseenter",()=>{S=e,H(i,S)}),t.addEventListener("click",()=>{N(r,i,n)})}function Ee(t,e,r,i){let n=w(".search-result-item",r);if(t.key==="ArrowDown"){if(!n.length)return;t.preventDefault(),S++,S>=n.length&&(S=0),H(r,S);return}if(t.key==="ArrowUp"){if(!n.length)return;t.preventDefault(),S--,S<0&&(S=n.length-1),H(r,S);return}if(t.key==="Enter"){let o=n[S];o&&(t.preventDefault(),N(e,r,i),R(o.href))}t.key==="Escape"&&N(e,r,i)}function ye(t){t.classList.remove("is-loading"),Gt(t)}function qt(t){Ot(t),t.classList.remove("is-loading")}function N(t,e,r){clearTimeout(k),x++,I=null,L?.abort(),S=-1,t.value="",F(e),qt(r)}var Vt=[["Router",Dt],["Prefetch",zt],["Copy",xt],["GlobalSearchController",Bt],["NavigationLoading",Ft],["RouterDebugPanel",async()=>{y.debug&&(await import("./chunks/debug-panel-GINEUXBW.js")).initRouterDebugPanel()}],["GlobalBackNavigation",wt],["GlobalErrorHandlers",Tt]];function m(t,e,r=null){let i,n=()=>i??=t().catch(a=>{throw i=void 0,a}),o=async()=>{let s=(await n())[e];if(typeof s!="function")throw new TypeError(`Initialiseur "${e}" introuvable.`);await s()};return o.preload=n,o.isRelevant=()=>r===null||document.querySelector(r)!==null,o}var Se=m(()=>import("./chunks/create-X2GTTKCB.js"),"initCreatePage"),Ae=m(()=>import("./chunks/acquire-release-NMA6KH5U.js"),"initAcquireRelease"),$e=m(()=>import("./chunks/edit-SGN3E6HW.js"),"initEditPage"),Ce=m(()=>import("./chunks/update-note-IX2LT6GR.js"),"initUpdateNote",".js-note-button"),Re=m(()=>import("./chunks/delete-manga-OW7SADRM.js"),"initDeleteManga",".js-delete-manga"),Te=m(()=>import("./chunks/delete-artbook-WWLILUZU.js"),"initDeleteArtbook",".js-delete-artbook"),ve=m(()=>import("./chunks/update-read-status-WSFFE75D.js"),"initUpdateReadStatus",".js-read-status-button"),Pe=m(()=>import("./chunks/create-S6TE7IM5.js"),"initCreatePage"),xe=m(()=>import("./chunks/delete-figurine-7ZVWP4UC.js"),"initDeleteFigurine",".js-delete-figurine"),Ie=m(()=>import("./chunks/update-collect-status-JMDO5NJI.js"),"initUpdateCollectStatus",".js-figurine-collect-status-button"),Le=m(()=>import("./chunks/create-X32KHW7P.js"),"initCreatePage"),Ne=m(()=>import("./chunks/delete-peluche-Q645ILPH.js"),"initDeletePeluche",".js-delete-peluche"),we=m(()=>import("./chunks/update-collect-status-HGJ7RJHT.js"),"initUpdatePelucheCollectStatus",".js-peluche-collect-status-button"),Ue=m(()=>import("./chunks/create-GLS3OKWG.js"),"initCreatePage"),ze=m(()=>import("./chunks/delete-nendoroid-LFAP37BN.js"),"initDeleteNendoroid",".js-delete-nendoroid"),De=m(()=>import("./chunks/update-collect-status-BIT4SYFY.js"),"initUpdateNendoroidCollectStatus",".js-nendoroid-collect-status-button"),je=m(()=>import("./chunks/create-YPUQJL7Q.js"),"initCreatePage"),Fe=m(()=>import("./chunks/flashcards-vocabulaire-JJXM54N7.js"),"initFlashcardsVocabulairePage"),He=m(()=>import("./chunks/flashcards-grammaire-H4LWFTUN.js"),"initFlashcardsGrammairePage"),ke=m(()=>import("./chunks/toggle-grammar-mastery-OFGPV2MM.js"),"initToggleGrammaireMaitrise",".grammar-ajax"),Ge=m(()=>import("./chunks/toggle-vocabulary-mastery-UW4W7454.js"),"initToggleVocabulaireMaitrise",".vocabulary-ajax"),Oe=m(()=>import("./chunks/delete-grammar-KECDRV6J.js"),"initDeleteGrammaire",".grammaire-delete"),Me=m(()=>import("./chunks/delete-vocabulary-YCQNJSJU.js"),"initDeleteVocabulaire",".vocabulaire-delete"),_e=m(()=>import("./chunks/customization-IR42DWNL.js"),"initProfileCustomization"),Ke=m(()=>import("./chunks/sql-OAFFDTXP.js"),"initSqlPage"),Qt=[{match:/^\/manga(?:\/|$)/,initializers:[["AcquireRelease",Ae],["UpdateNote",Ce],["DeleteManga",Re],["DeleteArtbook",Te],["UpdateReadStatus",ve]]},{match:/^\/manga\/ajouter\/(manga|artbook)\/?$/,initializers:[["AjouterMangaPage",Se]]},{match:/^\/manga\/series\/.+\/modifier\/\d+\/?$/,initializers:[["ModifierMangaPage",$e]]},{match:/^\/figurine(?:\/|$)/,initializers:[["DeleteFigurine",xe],["UpdateFigurineCollectStatus",Ie]]},{match:/^\/figurine\/ajouter\/?$/,initializers:[["AjouterFigurinePage",Pe]]},{match:/^\/peluche(?:\/|$)/,initializers:[["DeletePeluche",Ne],["UpdatePelucheCollectStatus",we]]},{match:/^\/peluche\/ajouter\/?$/,initializers:[["AjouterPeluchePage",Le]]},{match:/^\/nendoroid(?:\/|$)/,initializers:[["DeleteNendoroid",ze],["UpdateNendoroidCollectStatus",De]]},{match:/^\/nendoroid\/ajouter\/?$/,initializers:[["AjouterNendoroidPage",Ue]]},{match:/^\/chinois(?:\/|$)/,initializers:[["ToggleGrammaireMaitrise",ke],["ToggleVocabulaireMaitrise",Ge],["DeleteGrammaire",Oe],["DeleteVocabulaire",Me]]},{match:/^\/chinois\/ajouter\/(grammaire|vocabulaire)\/?$/,initializers:[["AjouterChinoisPage",je]]},{match:/^\/chinois\/flashcards\/vocabulaire\/?$/,initializers:[["FlashcardsVocabulaire",Fe]]},{match:/^\/chinois\/flashcards\/grammaire\/?$/,initializers:[["FlashcardsGrammaire",He]]},{match:/^\/profil\/personnalisation\/?$/,initializers:[["ProfileCustomization",_e]]},{match:/^\/sql\/?$/,initializers:[["SqlPage",Ke]]}];async function G(t,e){ct(t);try{await e(),l("INIT",`✅ ${t}`)}catch(r){$("INIT",r),C(r instanceof Error?r:new U(`Erreur pendant "${t}"`,{cause:r}))}finally{ut(t)}}async function Be(){for(let[t,e]of Vt)await G(t,e)}var O=0;async function Zt(){let t=++O,e=B(),r=Qt.filter(({match:i})=>i.test(e)).flatMap(({initializers:i})=>i);await Rt(r,G,()=>t===O&&e===B())}async function Xt(){l("APP","🚀 Boot"),y.isLocalhost&&await G("AppDebug",async()=>(await import("./chunks/app-debug-EBLO7KB7.js")).initAppDebug());let t=O;$t(Zt),await Be(),O===t&&await Zt(),await G("FlashToast",lt),l("APP","✅ Ready")}function Yt(){Xt().catch(t=>{$("APP",t),C(t)})}function Wt(){if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",Yt,{once:!0});return}Yt()}Wt();
