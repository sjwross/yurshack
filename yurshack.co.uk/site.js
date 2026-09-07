(() => {
  const toggle = document.querySelector("[data-nav-toggle]");
  const nav = document.getElementById("site-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  const reveals = document.querySelectorAll(".reveal");
  if (reveals.length && "IntersectionObserver" in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-in");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
    reveals.forEach((el) => io.observe(el));
  } else {
    reveals.forEach((el) => el.classList.add("is-in"));
  }

  // Domain arrangement soft-suggests related services on the order form
  const arrangement = document.querySelector("[name=domain_arrangement]");
  const setup = document.querySelector("[name='services[custom_domain_setup]']");
  const domain = document.querySelector("[name='services[co_uk_domain]']");
  if (arrangement instanceof HTMLSelectElement || arrangement instanceof HTMLInputElement) {
    const sync = () => {
      const value =
        arrangement instanceof HTMLSelectElement
          ? arrangement.value
          : document.querySelector("input[name=domain_arrangement]:checked")?.value;
      if (!(setup instanceof HTMLInputElement) || !(domain instanceof HTMLInputElement)) return;
      if (value === "platform_subdomain") {
        setup.checked = false;
        domain.checked = false;
      } else if (value === "customer_domain") {
        setup.checked = true;
        domain.checked = false;
      } else if (value === "provider_domain") {
        setup.checked = true;
        domain.checked = true;
      }
    };
    document.querySelectorAll("[name=domain_arrangement]").forEach((el) => {
      el.addEventListener("change", sync);
    });
  }

  // GitHub Pages has no PHP — open a draft email instead of posting.
  if (document.body.dataset.static === "1") {
    document.querySelectorAll("form[data-mail-to]").forEach((form) => {
      form.addEventListener("submit", (event) => {
        event.preventDefault();
        const to = form.getAttribute("data-mail-to") || "support@yurshack.com";
        const data = new FormData(form);
        const lines = [];
        data.forEach((value, key) => {
          if (key === "company_website") return;
          if (typeof value === "string" && value.trim() !== "") {
            lines.push(`${key}: ${value.trim()}`);
          }
        });
        const isOrder = /order/i.test(form.getAttribute("action") || "") || form.querySelector("[name=domain_arrangement]");
        const subject = encodeURIComponent(isOrder ? "Yur Shack order request" : "Yur Shack contact");
        const body = encodeURIComponent(lines.join("\n"));
        window.location.href = `mailto:${to}?subject=${subject}&body=${body}`;
      });
    });
  }
})();
