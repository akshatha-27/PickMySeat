//Movie scroll buttons
function scrollMovies(direction) {
  const carousel = document.querySelector(".movies-carousel");
  const scrollAmount = 300;

  if (direction === "left") {
    carousel.scrollBy({ left: -scrollAmount, behavior: "smooth" });
  } else {
    carousel.scrollBy({ left: scrollAmount, behavior: "smooth" });
  }
}

//Search movies
document.getElementById("searchInput").addEventListener("input", function () {
  const query = this.value.trim();
  const resultsList = document.getElementById("searchResults");

  if (query.length === 0) {
    resultsList.style.display = "none";
    resultsList.innerHTML = "";
    return;
  }

  fetch(`search.php?q=${encodeURIComponent(query)}`)
    .then((res) => res.json())
    .then((data) => {
      resultsList.innerHTML = "";

      if (data.length === 0) {
        resultsList.style.display = "none";
        return;
      }

      data.forEach((item) => {
        const li = document.createElement("li");

        // Choose icon based on type
        const iconClass =
          item.type === "movie" ? "fas fa-film" : "fas fa-map-marker-alt";

        li.innerHTML = `<i class="${iconClass}" style="margin-right: 8px; color: #ff4500;"></i> ${item.name}`;

        li.onclick = () => {
          if (item.type === "movie") {
            window.location.href = `book-now.php?id=${item.id}`;
          } else if (item.type === "theater") {
            window.location.href = `show-details.php?theater_id=${item.id}`;
          }
        };

        resultsList.appendChild(li);
      });

      resultsList.style.display = "block";
    });
});

document.addEventListener("click", function (e) {
  const searchBox = document.querySelector(".search-box");
  const resultsList = document.getElementById("searchResults");
  const searchInput = document.getElementById("searchInput");

  if (!searchBox.contains(e.target) && !resultsList.contains(e.target)) {
    resultsList.style.display = "none";
    resultsList.innerHTML = ""; // Clear the list
    searchInput.value = ""; // Clear the input box
  }
});

// Toggle profile dropdown
function toggleProfileMenu() {
  const menu = document.getElementById("profile-menu");
  menu.classList.toggle("show");
}

document.addEventListener("click", function (e) {
  const profileToggle = document.querySelector(".profile-toggle");
  const profileMenu = document.getElementById("profile-menu");

  // Close only if clicked outside the toggle area
  if (profileToggle && !profileToggle.contains(e.target)) {
    profileMenu.classList.remove("show");
  }
});

// Banner Carousel
let movies = [
  {
    image: "../images/banner2.jpg",
  },
  {
    image: "../images/banner1.jpg",
  },
  {
    image: "../images/banner3.jpg",
  },
];

const carousel = document.querySelector(".carousel");
let sliders = [];

let slideIndex = 0; // to track current slide index.

const createSlide = () => {
  if (slideIndex >= movies.length) {
    slideIndex = 0;
  }

  // creating DOM element
  let slide = document.createElement("div");
  let imgElement = document.createElement("img");

  // attaching all elements
  imgElement.appendChild(document.createTextNode(""));
  slide.appendChild(imgElement);
  carousel.appendChild(slide);

  // setting up image
  imgElement.src = movies[slideIndex].image;
  slideIndex++;

  // setting elements classname
  slide.className = "slider";

  sliders.push(slide);

  if (sliders.length) {
    sliders[0].style.marginLeft = `calc(-${100 * (sliders.length - 2)}% - ${
      10 * (sliders.length - 2)
    }px)`;
  }
};

for (let i = 0; i < 3; i++) {
  createSlide();
}

setInterval(() => {
  createSlide();
}, 6000);

function confirmLogout() {
  const confirmed = confirm("Are you sure you want to logout?");
  if (confirmed) {
    window.location.href = "../login/logout.php"; // Adjust path if needed
  }
}
