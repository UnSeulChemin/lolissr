import{a as r,c as p,f as T}from"./chunk-EKE4ZBKK.js";function d(t,s){let e=document.createElement("a");return e.href=t,e.className="search-result-item",e.innerHTML=s,e}function x(t,s,e){let l=encodeURIComponent(t.slug??""),o=Number(t.numero??0),n=t.livre??"",i=t.thumbnail??"default",a=t.extension??"jpg",h=t.thumbnailUrl??`${e}images/manga/thumbnail/${i}.${a}`,u=`${e}manga/series/${l}/${o}`;return d(u,`
            <img
                src="${r(h)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(n,s)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(o).padStart(2,"0")}
                </small>

            </span>
        `)}function R(t,s){let e=t.id??"",l=t.type??"",o=t.titre??"",n=t.description??"",i=String(t.langue??"").toLowerCase(),a=String(t.niveau??"").toLowerCase(),h=l==="grammaire"?"📖":"📚",u=l==="grammaire"?a.toUpperCase():i==="jinyu"?"晋语":"中文",c=l==="grammaire"?`${s}chinois/grammaire/${a}/recherche/${e}`:`${s}chinois/vocabulaire/${i}/recherche/${e}`;return d(c,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${r(h)}
                </span>

                <span class="search-result-category-label">
                    ${r(u)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${r(o)}
                </strong>

                <small class="search-result-meta">
                    ${r(n)}
                </small>

            </span>
        `)}function I(t,s,e){let l=encodeURIComponent(t.slug??""),o=Number(t.numero??0),n=t.waifu??"",i=t.origin??"",a=t.thumbnail??"default",h=t.extension??"jpg",u=t.thumbnailUrl??`${e}images/figurine/thumbnail/${a}.${h}`,c=`${e}figurine/figurines/${l}/${o}`;return d(c,`
            <img
                src="${r(u)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${p(i,s)}
                </small>

            </span>
        `)}function C(t,s,e){let l=encodeURIComponent(t.slug??""),o=Number(t.numero??0),n=t.waifu??"",i=t.origin??"",a=t.thumbnail??"default",h=t.extension??"jpg",u=t.thumbnailUrl??`${e}images/nendoroid/thumbnail/${a}.${h}`,c=`${e}nendoroid/nendoroids/${l}/${o}`;return d(c,`
            <img
                src="${r(u)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${p(i,s)}
                </small>

            </span>
        `)}function y(t,s,e){let l=encodeURIComponent(t.slug??""),o=Number(t.numero??0),n=t.waifu??"",i=t.origin??"",a=t.thumbnail??"default",h=t.extension??"jpg",u=t.thumbnailUrl??`${e}images/peluche/thumbnail/${a}.${h}`,c=`${e}peluche/peluches/${l}/${o}`;return d(c,`
            <img
                src="${r(u)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${p(i,s)}
                </small>

            </span>
        `)}function N(t,s,e){let l=encodeURIComponent(t.slug??""),o=Number(t.numero??0),n=t.artbook??"",i=t.auteur??"",a=t.serie??"",h=t.thumbnail??"default",u=t.extension??"jpg",c=t.thumbnailUrl??`${e}images/artbook/thumbnail/${h}.${u}`,$=`${e}manga/artbooks/${l}/${o}`,S=a||i||"Artbook";return d($,`
            <img
                src="${r(c)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${p(S,s)}
                </small>

            </span>
        `)}function U(t,s){let e=t.title??"",l=t.description??"",o=t.symbol??"→",n=t.url??"",i=`${s}${n}`;return d(i,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${r(o)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${r(e)}
                </strong>

                <small class="search-result-meta">
                    ${r(l)}
                </small>

            </span>
        `)}function E(t,s){let e=document.createElement("div");e.className="header-search-section-title",e.textContent=s,t.appendChild(e)}function b({title:t,results:s,buildItem:e,searchResults:l,setupResultItem:o,index:n}){return s.length===0||(E(l,t),s.forEach(i=>{let a=e(i);o(a,n),l.appendChild(a),n++})),n}function ht({mangas:t,categories:s=[],authors:e=[],artbooks:l,chinois:o,figurines:n,nendoroids:i,peluches:a,shortcuts:h,rawValue:u,basePath:c,searchResults:$,searchDropdown:S,setupResultItem:f,openDropdown:H,closeDropdown:O}){T($);let A=document.createDocumentFragment(),j=$;$=A;let m=0;for(let[g,v]of[["✨ CATÉGORIES MANGA",s],["✍️ AUTEURS MANGA",e]])m=b({title:g,results:v.slice(0,5),buildItem:M=>U(M,c),searchResults:$,setupResultItem:f,index:m});if(m=b({title:"📚 SÉRIES",results:t.slice(0,5),buildItem:g=>x(g,u,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"📕 ARTBOOKS",results:l.slice(0,5),buildItem:g=>N(g,u,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"⛩️ CHINOIS",results:o.slice(0,5),buildItem:g=>R(g,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🎀 FIGURINES",results:n.slice(0,5),buildItem:g=>I(g,u,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🪆 NENDOROIDS",results:i.slice(0,5),buildItem:g=>C(g,u,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"🧸 PELUCHES",results:a.slice(0,5),buildItem:g=>y(g,u,c),searchResults:$,setupResultItem:f,index:m}),m=b({title:"⚡ RACCOURCIS",results:h.slice(0,5),buildItem:g=>U(g,c),searchResults:$,setupResultItem:f,index:m}),j.appendChild(A),m===0){O(S);return}H(S)}export{ht as renderResults};
