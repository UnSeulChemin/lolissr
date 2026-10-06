import{a as r,c as g,f as A}from"./chunk-EKE4ZBKK.js";function d(t,s){let e=document.createElement("a");return e.href=t,e.className="search-result-item",e.innerHTML=s,e}function x(t,s,e){let l=encodeURIComponent(t.slug??""),i=Number(t.numero??0),n=t.livre??"",c=t.thumbnail??"default",o=t.extension??"jpg",h=t.thumbnailUrl??`${e}images/manga/thumbnail/${c}.${o}`,a=`${e}manga/series/${l}/${i}`;return d(a,`
            <img
                src="${r(h)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(i).padStart(2,"0")}
                </small>

            </span>
        `)}function R(t,s){let e=t.id??"",l=t.type??"",i=t.titre??"",n=t.description??"",c=String(t.langue??"").toLowerCase(),o=String(t.niveau??"").toLowerCase(),h=l==="grammaire"?"📖":"📚",a=l==="grammaire"?o.toUpperCase():c==="jinyu"?"晋语":"中文",u=l==="grammaire"?`${s}chinois/grammaire/${o}/recherche/${e}`:`${s}chinois/vocabulaire/${c}/recherche/${e}`;return d(u,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${r(h)}
                </span>

                <span class="search-result-category-label">
                    ${r(a)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${r(i)}
                </strong>

                <small class="search-result-meta">
                    ${r(n)}
                </small>

            </span>
        `)}function I(t,s,e){let l=encodeURIComponent(t.slug??""),i=Number(t.numero??0),n=t.waifu??"",c=t.origin??"",o=t.thumbnail??"default",h=t.extension??"jpg",a=t.thumbnailUrl??`${e}images/figurine/thumbnail/${o}.${h}`,u=`${e}figurine/figurines/${l}/${i}`;return d(u,`
            <img
                src="${r(a)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(c,s)}
                </small>

            </span>
        `)}function C(t,s,e){let l=encodeURIComponent(t.slug??""),i=Number(t.numero??0),n=t.waifu??"",c=t.origin??"",o=t.thumbnail??"default",h=t.extension??"jpg",a=t.thumbnailUrl??`${e}images/nendoroid/thumbnail/${o}.${h}`,u=`${e}nendoroid/nendoroids/${l}/${i}`;return d(u,`
            <img
                src="${r(a)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(c,s)}
                </small>

            </span>
        `)}function y(t,s,e){let l=encodeURIComponent(t.slug??""),i=Number(t.numero??0),n=t.waifu??"",c=t.origin??"",o=t.thumbnail??"default",h=t.extension??"jpg",a=t.thumbnailUrl??`${e}images/peluche/thumbnail/${o}.${h}`,u=`${e}peluche/peluches/${l}/${i}`;return d(u,`
            <img
                src="${r(a)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(c,s)}
                </small>

            </span>
        `)}function N(t,s,e){let l=encodeURIComponent(t.slug??""),i=Number(t.numero??0),n=t.artbook??"",c=t.auteur??"",o=t.serie??"",h=t.thumbnail??"default",a=t.extension??"jpg",u=t.thumbnailUrl??`${e}images/artbook/thumbnail/${h}.${a}`,$=`${e}manga/artbooks/${l}/${i}`,S=o||c||"Artbook";return d($,`
            <img
                src="${r(u)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(S,s)}
                </small>

            </span>
        `)}function U(t,s,e=""){let l=t.title??"",i=t.description??"",n=t.symbol??"→",c=t.url??"",o=`${s}${c}`;return d(o,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${r(n)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(l,e)}
                </strong>

                <small class="search-result-meta">
                    ${r(i)}
                </small>

            </span>
        `)}function E(t,s){let e=document.createElement("div");e.className="header-search-section-title",e.textContent=s,t.appendChild(e)}function b({title:t,results:s,buildItem:e,searchResults:l,setupResultItem:i,index:n}){return s.length===0||(E(l,t),s.forEach(c=>{let o=e(c);i(o,n),l.appendChild(o),n++})),n}function ht({mangas:t,categories:s=[],authors:e=[],artbooks:l,chinois:i,figurines:n,nendoroids:c,peluches:o,shortcuts:h,rawValue:a,basePath:u,searchResults:$,searchDropdown:S,setupResultItem:f,openDropdown:H,closeDropdown:O}){A($);let T=document.createDocumentFragment(),j=$;$=T;let m=0;m=b({title:"📚 SÉRIES",results:t.slice(0,5),buildItem:p=>x(p,a,u),searchResults:$,setupResultItem:f,index:m});for(let[p,v]of[["✨ CATÉGORIES MANGA",s],["✍️ AUTEURS MANGA",e]])m=b({title:p,results:v.slice(0,5),buildItem:M=>U(M,u,a),searchResults:$,setupResultItem:f,index:m});if(m=b({title:"📕 ARTBOOKS",results:l.slice(0,5),buildItem:p=>N(p,a,u),searchResults:$,setupResultItem:f,index:m}),m=b({title:"⛩️ CHINOIS",results:i.slice(0,5),buildItem:p=>R(p,u),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🎀 FIGURINES",results:n.slice(0,5),buildItem:p=>I(p,a,u),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🪆 NENDOROIDS",results:c.slice(0,5),buildItem:p=>C(p,a,u),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🧸 PELUCHES",results:o.slice(0,5),buildItem:p=>y(p,a,u),searchResults:$,setupResultItem:f,index:m}),m=b({title:"⚡ RACCOURCIS",results:h.slice(0,5),buildItem:p=>U(p,u),searchResults:$,setupResultItem:f,index:m}),j.appendChild(T),m===0){O(S);return}H(S)}export{ht as renderResults};
