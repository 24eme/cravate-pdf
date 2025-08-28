#! /bin/bash

for dir in ./procedures/LiberationReserveInterpro/submissions/*_CLOTURÉ; do
    DATAS="$dir/datas.json"
    METAS="$dir/metas.json"

    _ID=$(jq -r '.form.NUMCIVP' "$DATAS")

    _NBDEMANDE=0

    while read -r directory; do
        ((_NBDEMANDE++))

        if [[ $(realpath "$directory") == $(realpath "$dir") ]]; then
            break
        fi
    done < <(find "$dir/.." -maxdepth 1 -type d -name "*$_ID*_CLOTURÉ" | sort) # see: https://mywiki.wooledge.org/BashFAQ/024

    jq -rs --arg NBDEMANDE $_NBDEMANDE '[
        (
            .[0].form.PRODUIT | split("/") | .[6]
        ),
        .[0].form.MILLESIME,
        .[0].form.RAISON_SOCIALE,
        .[0].form.EMAIL,
        .[0].form.TELEPHONE,
        .[0].form.NUMCIVP,
        "%VOLUME_BLOQUE%",
        (.[0].form.VOLUME | gsub(","; ".")),
        "%VOLUME_RESTANT%",
        .[0].createdAt,
        .[0].form.CONDITION,
        (
            .[0].history | last | .date
        ),
        $NBDEMANDE
    ] | @csv' "$DATAS" "$METAS"
done
