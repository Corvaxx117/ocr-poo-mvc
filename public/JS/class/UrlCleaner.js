// Classe qui permet de supprimer dynamiquement plusieurs paramètres de l'URL
// Ajoute une option de délai configurable.
export default class UrlCleaner {
  /**
   * Supprime un ou plusieurs paramètres de l'URL sans recharger la page.
   * @param {Array|string} params - Paramètre(s) à supprimer
   */
  static removeQueryParams(params) {
    const url = new URL(window.location.href);
    let hasChanges = false;

    params.forEach((param) => {
      if (url.searchParams.has(param)) {
        url.searchParams.delete(param);
        hasChanges = true;
      }
    });

    // Mise à jour de l'URL uniquement si des modifications ont été effectuées
    if (hasChanges) {
      window.history.replaceState(null, "", url.pathname + url.search);
    }
  }
}
