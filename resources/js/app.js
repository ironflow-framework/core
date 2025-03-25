import "@css/app.css";
import "@css/errors.css";
// import "@uploads/*";

if (import.meta.hot) {
  import.meta.hot.accept();
}

document.addEventListener("DOMContentLoaded", () => {
  console.log("IronFlow frontend is ready via Vite ✅");
  
  initializeComponents();
});

function initializeComponents() {
  // Ajoutez ici l'initialisation de vos composants globaux
  // Par exemple : initialiser les tooltips, les modales, etc.
}
