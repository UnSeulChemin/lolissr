import{a as s,c as p,f as H}from"./chunk-SDYGVGSU.js";function d(t,n){let e=document.createElement("a");return e.href=t,e.className="search-result-item",e.innerHTML=n,e}function S(t,n,e){let m=encodeURIComponent(t.slug??""),c=Number(t.numero??0),r=t.livre??"",u=t.thumbnail??"default",l=t.extension??"jpg",i=t.thumbnailUrl??`${e}images/manga/thumbnail/${u}.${l}`,o=`${e}manga/series/${m}/${c}`;return d(o,`
            <img
                src="${s(i)}"
                alt="${s(r)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(r,n)}
                </strong>

                <small class="search-result-meta">
                    Tome ${String(c).padStart(2,"0")}
                </small>

            </span>
        `)}function x(t,n){let e=t.id??"",m=t.type??"",c=t.titre??"",r=t.description??"",u=String(t.langue??"").toLowerCase(),l=String(t.niveau??"").toLowerCase(),i=m==="grammaire"?"📖":"📚",o=m==="grammaire"?l.toUpperCase():u==="jinyu"?"晋语":"中文",a=m==="grammaire"?`${n}chinois/grammaire/${l}/recherche/${e}`:`${n}chinois/vocabulaire/${u}/recherche/${e}`;return d(a,`
            <span class="search-result-category">

                <span class="search-result-category-icon">
                    ${s(i)}
                </span>

                <span class="search-result-category-label">
                    ${s(o)}
                </span>

            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${s(c)}
                </strong>

                <small class="search-result-meta">
                    ${s(r)}
                </small>

            </span>
        `)}function R(t,n,e){let m=encodeURIComponent(t.slug??""),c=Number(t.numero??0),r=t.waifu??"",u=t.origin??"",l=t.thumbnail??"default",i=t.extension??"jpg",o=t.thumbnailUrl??`${e}images/figurine/thumbnail/${l}.${i}`,a=`${e}figurine/figurines/${m}/${c}`;return d(a,`
            <img
                src="${s(o)}"
                alt="${s(r)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(r,n)}
                </strong>

                <small class="search-result-meta">
                    ${p(u,n)}
                </small>

            </span>
        `)}function U(t,n,e){let m=encodeURIComponent(t.slug??""),c=Number(t.numero??0),r=t.waifu??"",u=t.origin??"",l=t.thumbnail??"default",i=t.extension??"jpg",o=t.thumbnailUrl??`${e}images/nendoroid/thumbnail/${l}.${i}`,a=`${e}nendoroid/nendoroids/${m}/${c}`;return d(a,`
            <img
                src="${s(o)}"
                alt="${s(r)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(r,n)}
                </strong>

                <small class="search-result-meta">
                    ${p(u,n)}
                </small>

            </span>
        `)}function C(t,n,e){let m=encodeURIComponent(t.slug??""),c=Number(t.numero??0),r=t.waifu??"",u=t.origin??"",l=t.thumbnail??"default",i=t.extension??"jpg",o=t.thumbnailUrl??`${e}images/peluche/thumbnail/${l}.${i}`,a=`${e}peluche/peluches/${m}/${c}`;return d(a,`
            <img
                src="${s(o)}"
                alt="${s(r)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(r,n)}
                </strong>

                <small class="search-result-meta">
                    ${p(u,n)}
                </small>

            </span>
        `)}function y(t,n,e){let m=encodeURIComponent(t.slug??""),c=Number(t.numero??0),r=t.artbook??"",u=t.auteur??"",l=t.serie??"",i=t.thumbnail??"default",o=t.extension??"jpg",a=t.thumbnailUrl??`${e}images/artbook/thumbnail/${i}.${o}`,h=`${e}manga/artbooks/${m}/${c}`,f=l||u||"Artbook";return d(h,`
            <img
                src="${s(a)}"
                alt="${s(r)}"
                loading="lazy"
                decoding="async"
            >

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${p(r,n)}
                </strong>

                <small class="search-result-meta">
                    ${p(f,n)}
                </small>

            </span>
        `)}function I(t,n){let e=t.title??"",m=t.description??"",c=t.symbol??"→",r=t.url??"",u=`${n}${r}`;return d(u,`
            <span
                class="search-result-icon"
                aria-hidden="true"
            >
                ${s(c)}
            </span>

            <span class="search-result-content">

                <strong class="search-result-title">
                    ${s(e)}
                </strong>

                <small class="search-result-meta">
                    ${s(m)}
                </small>

            </span>
        `)}function T(t,n){let e=document.createElement("div");e.className="header-search-section-title",e.textContent=n,t.appendChild(e)}function b({title:t,results:n,buildItem:e,searchInput:m,searchResults:c,searchDropdown:r,setupResultItem:u,index:l}){return n.length===0||(T(c,t),n.forEach(i=>{let o=e(i);u(o,l,m,c,r),c.appendChild(o),l++})),l}function gt({mangas:t,artbooks:n,chinois:e,figurines:m,nendoroids:c,peluches:r,shortcuts:u,rawValue:l,basePath:i,searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,openDropdown:E,closeDropdown:j}){H(a);let N=document.createDocumentFragment(),v=a;a=N;let g=0;if(g=b({title:"📚 SÉRIES",results:t.slice(0,5),buildItem:$=>S($,l,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"📕 ARTBOOKS",results:n.slice(0,5),buildItem:$=>y($,l,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"⛩️ CHINOIS",results:e.slice(0,5),buildItem:$=>x($,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"🎀 FIGURINES",results:m.slice(0,5),buildItem:$=>R($,l,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"🪆 NENDOROIDS",results:c.slice(0,5),buildItem:$=>U($,l,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"🧸 PELUCHES",results:r.slice(0,5),buildItem:$=>C($,l,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),g=b({title:"⚡ RACCOURCIS",results:u.slice(0,5),buildItem:$=>I($,i),searchInput:o,searchResults:a,searchDropdown:h,setupResultItem:f,index:g}),v.appendChild(N),g===0){j(h);return}E(h)}export{gt as renderResults};
