import HoverEffect from "./class/HoverEffect.js";
import UrlCleaner from "./class/UrlCleaner.js";

const hoverEffect = new HoverEffect();
const urlCleaner = new UrlCleaner();

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    UrlCleaner.removeQueryParams(["success", "error"]);
  }, 1000); // Supprime après 1s pour laisser le temps au message de s'afficher
});
