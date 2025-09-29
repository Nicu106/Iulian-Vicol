/**
 * Saved Vehicles Page Specific JavaScript
 * Gestionează funcționalitățile specifice paginii de mașini salvate
 */
document.addEventListener('DOMContentLoaded', function() {
  // Inițializează managerul dacă nu există
  if (!window.savedVehiclesManager) {
    console.error('SavedVehiclesManager nu este disponibil');
    return;
  }

  const manager = window.savedVehiclesManager;
  
  // Actualizează UI-ul pentru această pagină
  manager.updateSavedVehiclesPage();
  
  // Adaugă funcționalități specifice paginii
  initializePageFeatures();
});

/**
 * Inițializează funcționalitățile specifice paginii
 */
function initializePageFeatures() {
  // Adaugă funcționalitate de căutare în mașinile salvate
  addSearchFunctionality();
  
  // Adaugă funcționalitate de sortare
  addSortingFunctionality();
  
  // Adaugă funcționalitate de filtrare
  addFilteringFunctionality();
}

/**
 * Adaugă funcționalitatea de căutare
 */
function addSearchFunctionality() {
  const searchContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
  if (!searchContainer) return;

  const searchHtml = `
    <div class="d-flex gap-2">
      <div class="input-group" style="max-width: 300px;">
        <input type="text" class="form-control" id="searchSavedVehicles" placeholder="Caută în mașinile salvate...">
        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </div>
  `;
  
  searchContainer.insertAdjacentHTML('beforeend', searchHtml);
  
  // Bind events pentru căutare
  document.getElementById('searchSavedVehicles').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    filterSavedVehicles(searchTerm);
  });
  
  document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('searchSavedVehicles').value = '';
    filterSavedVehicles('');
  });
}

/**
 * Adaugă funcționalitatea de sortare
 */
function addSortingFunctionality() {
  const searchContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
  if (!searchContainer) return;

  const sortHtml = `
    <div class="dropdown">
      <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-sort-down me-1"></i>Sortează
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#" data-sort="title">Nume A-Z</a></li>
        <li><a class="dropdown-item" href="#" data-sort="title-desc">Nume Z-A</a></li>
        <li><a class="dropdown-item" href="#" data-sort="price">Preț crescător</a></li>
        <li><a class="dropdown-item" href="#" data-sort="price-desc">Preț descrescător</a></li>
        <li><a class="dropdown-item" href="#" data-sort="savedAt">Data salvare (recent)</a></li>
        <li><a class="dropdown-item" href="#" data-sort="savedAt-desc">Data salvare (vechi)</a></li>
      </ul>
    </div>
  `;
  
  searchContainer.insertAdjacentHTML('beforeend', sortHtml);
  
  // Bind events pentru sortare
  document.querySelectorAll('[data-sort]').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const sortType = this.dataset.sort;
      sortSavedVehicles(sortType);
      
      // Actualizează textul butonului
      document.querySelector('.btn-outline-secondary.dropdown-toggle').innerHTML = 
        `<i class="bi bi-sort-down me-1"></i>${this.textContent}`;
    });
  });
}

/**
 * Adaugă funcționalitatea de filtrare
 */
function addFilteringFunctionality() {
  const searchContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4');
  if (!searchContainer) return;

  const filterHtml = `
    <div class="dropdown">
      <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-funnel me-1"></i>Filtre
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item" href="#" data-filter="all">Toate</a></li>
        <li><a class="dropdown-item" href="#" data-filter="with-notes">Cu note</a></li>
        <li><a class="dropdown-item" href="#" data-filter="without-notes">Fără note</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="#" data-filter="price-0-10000">Preț: 0-10.000€</a></li>
        <li><a class="dropdown-item" href="#" data-filter="price-10000-25000">Preț: 10.000-25.000€</a></li>
        <li><a class="dropdown-item" href="#" data-filter="price-25000+">Preț: 25.000€+</a></li>
      </ul>
    </div>
  `;
  
  searchContainer.insertAdjacentHTML('beforeend', filterHtml);
  
  // Bind events pentru filtrare
  document.querySelectorAll('[data-filter]').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault();
      const filterType = this.dataset.filter;
      applyFilter(filterType);
      
      // Actualizează textul butonului
      document.querySelector('.btn-outline-secondary.dropdown-toggle:last-of-type').innerHTML = 
        `<i class="bi bi-funnel me-1"></i>${this.textContent}`;
    });
  });
}

/**
 * Filtrează mașinile salvate după termenul de căutare
 */
function filterSavedVehicles(searchTerm) {
  if (!window.savedVehiclesManager) return;
  
  const manager = window.savedVehiclesManager;
  const vehicles = manager.savedVehicles;
  
  if (!searchTerm) {
    // Afișează toate mașinile
    manager.updateSavedVehiclesPage();
    return;
  }
  
  const filteredVehicles = vehicles.filter(vehicle => 
    vehicle.title.toLowerCase().includes(searchTerm) ||
    (vehicle.notes && vehicle.notes.toLowerCase().includes(searchTerm)) ||
    vehicle.price.toString().includes(searchTerm)
  );
  
  displayFilteredVehicles(filteredVehicles);
}

/**
 * Sortează mașinile salvate
 */
function sortSavedVehicles(sortType) {
  if (!window.savedVehiclesManager) return;
  
  const manager = window.savedVehiclesManager;
  const vehicles = [...manager.savedVehicles];
  
  switch (sortType) {
    case 'title':
      vehicles.sort((a, b) => a.title.localeCompare(b.title));
      break;
    case 'title-desc':
      vehicles.sort((a, b) => b.title.localeCompare(a.title));
      break;
    case 'price':
      vehicles.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
      break;
    case 'price-desc':
      vehicles.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
      break;
    case 'savedAt':
      vehicles.sort((a, b) => new Date(a.savedAt) - new Date(b.savedAt));
      break;
    case 'savedAt-desc':
      vehicles.sort((a, b) => new Date(b.savedAt) - new Date(a.savedAt));
      break;
  }
  
  displayFilteredVehicles(vehicles);
}

/**
 * Aplică filtre pe mașinile salvate
 */
function applyFilter(filterType) {
  if (!window.savedVehiclesManager) return;
  
  const manager = window.savedVehiclesManager;
  const vehicles = manager.savedVehicles;
  
  let filteredVehicles = [];
  
  switch (filterType) {
    case 'all':
      filteredVehicles = vehicles;
      break;
    case 'with-notes':
      filteredVehicles = vehicles.filter(v => v.notes && v.notes.trim() !== '');
      break;
    case 'without-notes':
      filteredVehicles = vehicles.filter(v => !v.notes || v.notes.trim() === '');
      break;
    case 'price-0-10000':
      filteredVehicles = vehicles.filter(v => parseFloat(v.price) <= 10000);
      break;
    case 'price-10000-25000':
      filteredVehicles = vehicles.filter(v => parseFloat(v.price) > 10000 && parseFloat(v.price) <= 25000);
      break;
    case 'price-25000+':
      filteredVehicles = vehicles.filter(v => parseFloat(v.price) > 25000);
      break;
  }
  
  displayFilteredVehicles(filteredVehicles);
}

/**
 * Afișează mașinile filtrate
 */
function displayFilteredVehicles(vehicles) {
  const container = document.getElementById('savedVehiclesContainer');
  const noVehiclesDiv = document.getElementById('noSavedVehicles');
  
  if (!container) return;

  if (vehicles.length === 0) {
    container.innerHTML = `
      <div class="col-12 text-center py-5">
        <i class="bi bi-search text-muted mb-3" style="font-size: 4rem;"></i>
        <h4 class="text-muted">Nu am găsit mașini</h4>
        <p class="text-muted">Încearcă să modifici filtrele sau termenul de căutare.</p>
      </div>
    `;
    if (noVehiclesDiv) noVehiclesDiv.style.display = 'none';
  } else {
    if (noVehiclesDiv) noVehiclesDiv.style.display = 'none';
    
    const vehiclesHtml = vehicles.map(vehicle => createVehicleCardHtml(vehicle)).join('');
    container.innerHTML = vehiclesHtml;
    
    // Re-bind events pentru butoanele nou create
    bindCardEvents();
  }
}

/**
 * Creează HTML-ul pentru un card de mașină
 */
function createVehicleCardHtml(vehicle) {
  return `
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
      <div class="card h-100 saved-vehicle-card" data-vehicle-id="${vehicle.id}">
        <img src="${vehicle.image || 'https://via.placeholder.com/480x320/f8f9fa/6c757d?text=Fără+imagine'}" 
             class="card-img-top" alt="${vehicle.title}"
             loading="lazy"
             onerror="this.src='https://via.placeholder.com/480x320/f8f9fa/6c757d?text=Fără+imagine'">
        <div class="card-body d-flex flex-column">
          <h6 class="card-title mb-1" style="min-height: 2.5rem;">${vehicle.title}</h6>
          <div class="vehicle-info">
            <div class="price">€ ${formatPrice(vehicle.price)}</div>
            ${vehicle.notes ? `<div class="small text-muted mt-1"><i class="bi bi-stickies"></i> ${vehicle.notes}</div>` : ''}
          </div>
          <div class="mt-auto card-actions">
            <div class="d-flex gap-1">
              <button type="button" class="btn btn-sm btn-edit edit-notes-btn" data-vehicle-id="${vehicle.id}" title="Editează notele">
                <i class="bi bi-pencil"></i>
              </button>
              <button type="button" class="btn btn-sm btn-remove remove-vehicle-btn" data-vehicle-id="${vehicle.id}" title="Elimină din salvate">
                <i class="bi bi-trash"></i>
              </button>
            </div>
            <a href="/vehicles/${vehicle.slug}" class="btn btn-sm btn-primary">Detalii</a>
          </div>
        </div>
      </div>
    </div>
  `;
}

/**
 * Formatează prețul
 */
function formatPrice(price) {
  if (typeof price === 'string') {
    const numericPrice = price.replace(/[^\d]/g, '');
    return numericPrice ? parseInt(numericPrice).toLocaleString() : '0';
  }
  return price ? price.toLocaleString() : '0';
}

/**
 * Leagă evenimentele pentru cardurile de mașini
 */
function bindCardEvents() {
  // Butoane de eliminare
  document.querySelectorAll('.remove-vehicle-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const vehicleId = this.dataset.vehicleId;
      if (window.savedVehiclesManager) {
        window.savedVehiclesManager.removeVehicle(vehicleId);
      }
    });
  });

  // Butoane de editare note
  document.querySelectorAll('.edit-notes-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const vehicleId = this.dataset.vehicleId;
      if (window.savedVehiclesManager) {
        window.savedVehiclesManager.showNotesEditor(vehicleId);
      }
    });
  });
}
