  const salons = [
{
    name: 'Salón Imperial',
    rating: '5.0 · 128 reseñas',
    price: '$1.800',
    capacity: '250 personas',
    tables: '24 mesas',
    location: 'Palermo, Buenos Aires',
    description: [
      'Un salón de estilo clásico con detalles en dorado y una propuesta elegante para bodas, recepciones y eventos corporativos.',
      'Su gran pista de baile, iluminación ambientada y jardín exterior lo convierten en una opción ideal para celebraciones memorables.',
      'El espacio está preparado para ofrecer comodidad, sofisticación y una experiencia completa a cada invitado.'
    ],
    services: ['Aire acondicionado', 'WiFi', 'Cocina equipada', 'Estacionamiento privado', 'Seguridad', 'Acceso para personas con movilidad reducida', 'Jardín', 'Escenario'],
    gallery: [
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80'
    ]
  },
  {
    name: 'Salón Toscana',
    rating: '4.8 · 94 reseñas',
    price: '$1.500',
    capacity: '180 personas',
    tables: '18 mesas',
    location: 'Belgrano, Buenos Aires',
    description: [
      'Toscana combina arquitectura contemporánea con una atmósfera cálida y acogedora, ideal para ceremonias íntimas y celebraciones elegantes.',
      'Sus amplios ventanales, jardín privado y zonas de descanso brindan confort y una vista espectacular para los invitados.',
      'Cada detalle fue pensado para trabajar con estilo, funcionalidad y una gran versatilidad de uso.'
    ],
    services: ['Aire acondicionado', 'WiFi', 'Cocina equipada', 'Estacionamiento privado', 'Seguridad', 'Acceso para personas con movilidad reducida', 'Jardín', 'Escenario'],
    gallery: [
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80'
    ]
  },
  {
    name: 'Salón Victoria',
    rating: '4.9 · 111 reseñas',
    price: '$1.700',
    capacity: '220 personas',
    tables: '22 mesas',
    location: 'Recoleta, Buenos Aires',
    description: [
      'Victoria ofrece una combinación de lujo, sofisticación y funcionalidad para eventos de alto impacto.',
      'Su diseño refinado, iluminación premium y amplios sectores de recepción permiten crear experiencias memorables.',
      'Es ideal para celebraciones con estilo, comodidad y una logística perfectamente organizada.'
    ],
    services: ['Aire acondicionado', 'WiFi', 'Cocina equipada', 'Estacionamiento privado', 'Seguridad', 'Acceso para personas con movilidad reducida', 'Jardín', 'Escenario'],
    gallery: [
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80'
    ]
  },
  {
    name: 'Salón París',
    rating: '4.7 · 87 reseñas',
    price: '$1.400',
    capacity: '160 personas',
    tables: '16 mesas',
    location: 'Villa Crespo, Buenos Aires',
    description: [
      'París destaca por su estética refinada y su propuesta versátil para eventos íntimos o de mayor envergadura.',
      'Combina ambientes luminosos, decoración elegante y un excelente manejo del espacio para lograr un impacto visual único.',
      'Es una excelente opción para quienes buscan un lugar contemporáneo con personalidad y encanto.'
    ],
    services: ['Aire acondicionado', 'WiFi', 'Cocina equipada', 'Estacionamiento privado', 'Seguridad', 'Acceso para personas con movilidad reducida', 'Jardín', 'Escenario'],
    gallery: [
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80'
    ],
    reviews: [
      { user: 'Agustina D.', stars: '★★★★★', date: '08 ago 2025', text: 'Muy cálido, con un diseño espectacular y atención personalizada.' },
      { user: 'Diego C.', stars: '★★★★☆', date: '21 jul 2025', text: 'Ideal para eventos con estilo moderno y buena organización.' },
      { user: 'Micaela F.', stars: '★★★★★', date: '09 jul 2025', text: 'Nos encantó la iluminación y la comodidad del lugar.' }
    ]
  },
  {
    name: 'Salón Sunset',
    rating: '5.0 · 103 reseñas',
    price: '$1.900',
    capacity: '280 personas',
    tables: '28 mesas',
    location: 'Puerto Madero, Buenos Aires',
    description: [
      'Sunset fusiona elegancia y modernidad con una vista privilegiada y un espacio pensado para eventos de alto nivel.',
      'Su diseño amplio, ambiente sofisticado y amenities de primer nivel lo convierten en una elección destacada para grandes celebraciones.',
      'Ofrece una experiencia única donde cada detalle destaca por su calidad y armonía.'
    ],
    services: ['Aire acondicionado', 'WiFi', 'Cocina equipada', 'Estacionamiento privado', 'Seguridad', 'Acceso para personas con movilidad reducida', 'Jardín', 'Escenario'],
    gallery: [
      'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
      'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=900&q=80'
    ],
    reviews: [
      { user: 'Florencia L.', stars: '★★★★★', date: '15 ago 2025', text: 'Un lugar espectacular para celebraciones de gala. Muy recomendable.' },
      { user: 'Bruno P.', stars: '★★★★★', date: '04 ago 2025', text: 'Excelente distribución del espacio y una atención sobresaliente.' },
      { user: 'Mauro R.', stars: '★★★★☆', date: '29 jul 2025', text: 'Muy elegante y con todos los detalles que uno espera para un gran evento.' }
    ]
  }
];

const salonSelector = document.getElementById('salonSelector');
const mainImage = document.getElementById('mainImage');
const thumbs = document.getElementById('thumbs');
const salonName = document.getElementById('salonName');
const salonRating = document.getElementById('salonRating');
const salonPrice = document.getElementById('salonPrice');
const salonCapacity = document.getElementById('salonCapacity');
const salonTables = document.getElementById('salonTables');
const salonLocation = document.getElementById('salonLocation');
const salonDescription = document.getElementById('salonDescription');
const servicesList = document.getElementById('servicesList');
const reviewsGrid = document.getElementById('reviewsGrid');

let selectedSalonIndex = 0;

function renderSalonCards() {
  salonSelector.innerHTML = salons
    .map((salon, index) => `
      <button class="salon-card ${index === selectedSalonIndex ? 'active' : ''}" data-index="${index}">
        <img src="${salon.gallery[0]}" alt="${salon.name}" />
        <h3>${salon.name}</h3>
      </button>
    `)
    .join('');

  document.querySelectorAll('.salon-card').forEach((button) => {
    button.addEventListener('click', () => {
      selectedSalonIndex = Number(button.dataset.index);
      renderSalonCards();
      updateSalonDetails();
    });
  });
}

function updateSalonDetails() {
  const salon = salons[selectedSalonIndex];

  salonName.textContent = salon.name;
  salonRating.textContent = salon.rating;
  salonPrice.textContent = salon.price;
  salonCapacity.textContent = salon.capacity;
  salonTables.textContent = salon.tables;
  salonLocation.textContent = salon.location;
  salonDescription.innerHTML = salon.description.map((paragraph) => `<p>${paragraph}</p>`).join('');
  servicesList.innerHTML = salon.services.map((service) => `<li>${service}</li>`).join('');

  mainImage.src = salon.gallery[0];
  mainImage.alt = salon.name;
  thumbs.innerHTML = salon.gallery
    .map((image, index) => `
      <button class="thumb-btn ${index === 0 ? 'active' : ''}" data-image="${image}" data-index="${index}">
        <img src="${image}" alt="${salon.name} ${index + 1}" />
      </button>
    `)
    .join('');

  document.querySelectorAll('.thumb-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const selectedImage = button.dataset.image;
      mainImage.src = selectedImage;
      document.querySelectorAll('.thumb-btn').forEach((thumb) => thumb.classList.remove('active'));
      button.classList.add('active');
    });
  });

  reviewsGrid.innerHTML = salon.reviews
    .map(
      (review) => `
        <article class="review-card">
          <div class="review-top">
            <img class="avatar" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80" alt="${review.user}" />
            <div>
              <p class="review-user">${review.user}</p>
              <p class="review-meta">${review.date}</p>
            </div>
          </div>
          <p class="review-stars">${review.stars}</p>
          <p class="review-text">${review.text}</p>
        </article>
      `
    )
    .join('');
}

renderSalonCards();
updateSalonDetails();
