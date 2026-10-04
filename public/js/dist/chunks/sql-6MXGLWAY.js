import{a as q}from"./chunk-BF3LETK2.js";import{a as f}from"./chunk-HXYIU4QO.js";import{a as c,d as p}from"./chunk-4A3SIPZT.js";import{c as m}from"./chunk-XGKE6S5F.js";import{b as u}from"./chunk-UIBCKW3Z.js";var h=!1;function l(t){return String(t).replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;")}function E(t,e){let r=document.getElementById("sql-results");r&&(r.innerHTML=`
    <section
        class="
            home-grid
            home-grid-top
            card-grid-3
            sql-grid
        "
    >

        <article
            class="
                card
                transition-card
                card-medium
                sql-query-card
            "
        >

            <h2 class="home-card-title">
                📝 Requête SQL
            </h2>

            <pre class="sql-result-query">${l(t)}</pre>

        </article>

        <article
            class="
                card
                transition-card
                card-link-wide
                card-wide
                sql-result-card
            "
        >

            <h2 class="home-card-title">
                ❌ Erreur
            </h2>

            <p class="sql-error">
                ${l(e)}
            </p>

        </article>

    </section>
    `)}function y(t,e,r=!1){let i=document.getElementById("sql-results");if(!i)return;if(e.length===0){i.innerHTML=`
        <section
            class="
                home-grid
                home-grid-top
                card-grid-3
                sql-grid
            "
        >

            <article
                class="
                    card
                    transition-card
                    card-medium
                    sql-query-card
                "
            >

                <h2 class="home-card-title">
                    📝 Requête SQL
                </h2>

                <pre class="sql-result-query">${l(t)}</pre>

            </article>

            <article
                class="
                    card
                    transition-card
                    card-link-wide
                    card-wide
                    sql-result-card
                "
            >

                <h2 class="home-card-title">
                    📊 Résultat
                </h2>

                <p class="sql-result-count">
                    Aucune ligne retournée.
                </p>

            </article>

        </section>
        `;return}let n=Object.keys(e[0]),o=n.map(s=>`<th>${l(s)}</th>`).join(""),a=e.map(s=>`
                <tr>
                    ${n.map(d=>`<td>${l(s[d]??"")}</td>`).join("")}
                </tr>
                `).join("");i.innerHTML=`
    <section
        class="
            home-grid
            home-grid-top
            card-grid-3
            sql-grid
        "
    >

        <article
            class="
                card
                transition-card
                card-medium
                sql-query-card
            "
        >

            <h2 class="home-card-title">
                📝 Requête SQL
            </h2>

            <pre class="sql-result-query">${l(t)}</pre>

        </article>

        <article
            class="
                card
                transition-card
                card-link-wide
                card-wide
                sql-result-card
            "
        >

            <h2 class="home-card-title">
                📊 Résultat
            </h2>

            <p class="sql-result-count">
                ${e.length} ligne(s)${r?" — Résultat tronqué. Affine la requête ou utilise LIMIT/OFFSET pour consulter la suite.":""}
            </p>

            <div class="sql-table-wrapper">

                <table class="sql-table">

                    <thead>
                        <tr>
                            ${o}
                        </tr>
                    </thead>

                    <tbody>
                        ${a}
                    </tbody>

                </table>

            </div>

        </article>

    </section>
    `}async function w(t){let e="";try{if(t.dataset.loading==="1")return;let r=t.dataset.url;if(!r)throw new c("URL SQL manquante.");let i=t.querySelector("#sql");if(!(i instanceof HTMLTextAreaElement))throw new c("Champ SQL introuvable.");if(e=i.value.trim(),e==="")throw new c("Veuillez saisir une requête SQL.");let n=/^\s*(DELETE|DROP|TRUNCATE)\b/i.test(e),o=/^\s*(INSERT|UPDATE|ALTER|CREATE|RENAME|REPLACE)\b/i.test(e);if(n){if(!await q("Cette requête va supprimer des données. Continuer ?"))return}else if(o&&!await f({title:"Modification",message:"Cette requête va modifier la base de données. Continuer ?"}))return;t.dataset.loading="1";let a=await p(r,{sql:e});if(a?.success!==!0)throw new c(a?.message||"Erreur SQL.");let s=Array.isArray(a.data?.result)?a.data.result:[];y(e,s,a.data?.truncated===!0),u("SQL","success",{rows:s.length})}catch(r){E(e,r instanceof Error?r.message:"Erreur inconnue")}finally{delete t.dataset.loading}}function g(){h||(h=!0,m(document,"submit","[data-sql-form]",(t,e)=>{e instanceof HTMLFormElement&&(t.preventDefault(),w(e))}),u("SQL","initialized"))}function $(){g()}export{$ as initSqlPage};
