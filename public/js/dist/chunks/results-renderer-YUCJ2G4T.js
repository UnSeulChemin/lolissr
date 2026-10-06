import{a as r,c as g,f as H}from"./chunk-EKE4ZBKK.js";function p(t,s){let e=document.createElement("a");return e.href=t,e.className="search-result-item",e.innerHTML=s,e}function S(t,s,e){let i=encodeURIComponent(t.slug??""),a=Number(t.numero??0),n=t.livre??"",u=t.thumbnail??"default",o=t.extension??"jpg",c=t.thumbnailUrl??`${e}images/manga/thumbnail/${u}.${o}`,l=`${e}manga/series/${i}/${a}`;return p(l,`
            <img
                src="${r(c)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(a).padStart(2,"0")}
                </small>

            </span>
        `)}function x(t,s){let e=t.id??"",i=t.type??"",a=t.titre??"",n=t.description??"",u=String(t.langue??"").toLowerCase(),o=String(t.niveau??"").toLowerCase(),c=i==="grammaire"?"📖":"📚",l=i==="grammaire"?o.toUpperCase():u==="jinyu"?"晋语":"中文",h=i==="grammaire"?`${s}chinois/grammaire/${o}/recherche/${e}`:`${s}chinois/vocabulaire/${u}/recherche/${e}`;return p(h,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${r(c)}
                </span>

                <span class="search-result-category-label">
                    ${r(l)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${r(a)}
                </strong>

                <small class="search-result-meta">
                    ${r(n)}
                </small>

            </span>
        `)}function R(t,s,e){let i=encodeURIComponent(t.slug??""),a=Number(t.numero??0),n=t.waifu??"",u=t.origin??"",o=t.thumbnail??"default",c=t.extension??"jpg",l=t.thumbnailUrl??`${e}images/figurine/thumbnail/${o}.${c}`,h=`${e}figurine/figurines/${i}/${a}`;return p(h,`
            <img
                src="${r(l)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(u,s)}
                </small>

            </span>
        `)}function U(t,s,e){let i=encodeURIComponent(t.slug??""),a=Number(t.numero??0),n=t.waifu??"",u=t.origin??"",o=t.thumbnail??"default",c=t.extension??"jpg",l=t.thumbnailUrl??`${e}images/nendoroid/thumbnail/${o}.${c}`,h=`${e}nendoroid/nendoroids/${i}/${a}`;return p(h,`
            <img
                src="${r(l)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(u,s)}
                </small>

            </span>
        `)}function I(t,s,e){let i=encodeURIComponent(t.slug??""),a=Number(t.numero??0),n=t.waifu??"",u=t.origin??"",o=t.thumbnail??"default",c=t.extension??"jpg",l=t.thumbnailUrl??`${e}images/peluche/thumbnail/${o}.${c}`,h=`${e}peluche/peluches/${i}/${a}`;return p(h,`
            <img
                src="${r(l)}"
                alt="${r(n)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${g(n,s)}
                </strong>

                <small class="search-result-meta">
                    ${g(u,s)}
                </small>

            </span>
        `)}function C(t,s,e){let i=encodeURIComponent(t.slug??""),a=Number(t.numero??0),n=t.artbook??"",u=t.auteur??"",o=t.serie??"",c=t.thumbnail??"default",l=t.extension??"jpg",h=t.thumbnailUrl??`${e}images/artbook/thumbnail/${c}.${l}`,$=`${e}manga/artbooks/${i}/${a}`,b=o||u||"Artbook";return p($,`
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
                    ${g(b,s)}
                </small>

            </span>
        `)}function y(t,s){let e=t.title??"",i=t.description??"",a=t.symbol??"→",n=t.url??"",u=`${s}${n}`;return p(u,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${r(a)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${r(e)}
                </strong>

                <small class="search-result-meta">
                    ${r(i)}
                </small>

            </span>
        `)}function T(t,s){let e=document.createElement("div");e.className="header-search-section-title",e.textContent=s,t.appendChild(e)}function f({title:t,results:s,buildItem:e,searchResults:i,setupResultItem:a,index:n}){return s.length===0||(T(i,t),s.forEach(u=>{let o=e(u);a(o,n),i.appendChild(o),n++})),n}function mt({mangas:t,artbooks:s,chinois:e,figurines:i,nendoroids:a,peluches:n,shortcuts:u,rawValue:o,basePath:c,searchResults:l,searchDropdown:h,setupResultItem:$,openDropdown:b,closeDropdown:E}){H(l);let N=document.createDocumentFragment(),j=l;l=N;let m=0;if(m=f({title:"📚 SÉRIES",results:t.slice(0,5),buildItem:d=>S(d,o,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"📕 ARTBOOKS",results:s.slice(0,5),buildItem:d=>C(d,o,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"⛩️ CHINOIS",results:e.slice(0,5),buildItem:d=>x(d,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"🎀 FIGURINES",results:i.slice(0,5),buildItem:d=>R(d,o,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"🪆 NENDOROIDS",results:a.slice(0,5),buildItem:d=>U(d,o,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"🧸 PELUCHES",results:n.slice(0,5),buildItem:d=>I(d,o,c),searchResults:l,setupResultItem:$,index:m}),m=f({title:"⚡ RACCOURCIS",results:u.slice(0,5),buildItem:d=>y(d,c),searchResults:l,setupResultItem:$,index:m}),j.appendChild(N),m===0){E(h);return}b(h)}export{mt as renderResults};
