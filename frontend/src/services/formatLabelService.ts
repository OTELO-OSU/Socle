import type { Unite } from "./dictionnaireService";

export const formatUniteLabel = (unite: Unite) => {
    let str = ""
    if (unite.acronyme) str += `${unite.acronyme} - `
    str += unite.nom
    if (unite.codeUnite) str += ` (${unite.codeUnite})`
    return str
}