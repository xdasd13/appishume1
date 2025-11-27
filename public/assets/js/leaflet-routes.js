/**
 * Gestión de mapas y rutas con Leaflet + OpenStreetMap
 */

const LeafletRoutes = {
    map: null,
    userMarker: null,
    destinationMarker: null,
    routeLayer: null,
    userLocation: null,
    modalElement: null,

    /**
     * Inicializa el modal y carga el mapa
     */
    initModal() {
        // Crear modal HTML
        const modalHTML = `
            <div class="modal fade" id="routeModal" tabindex="-1" aria-labelledby="routeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="routeModalLabel">
                                <i class="fas fa-map-marked-alt me-2"></i>Ruta hacia la Ubicación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="mapContainer" style="height: 500px; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                                <div id="leafletMap" style="height: 100%; width: 100%;"></div>
                            </div>
                            <div id="routeInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                        <div>
                                            <strong>Distancia:</strong> <span id="routeDistance">-</span>
                                        </div>
                                        <div>
                                            <strong>Duración estimada:</strong> <span id="routeDuration">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="loadingRoute" style="text-align: center; display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando ruta...</span>
                                </div>
                                <p class="mt-2">Calculando ruta...</p>
                            </div>
                            <div id="errorRoute" style="display: none;" class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><span id="errorMessage"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a id="openGoogleMaps" href="#" target="_blank" class="btn btn-success" style="display: none;">
                                <i class="fas fa-directions me-2"></i>Abrir en Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insertar modal en el DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modalElement = new bootstrap.Modal(document.getElementById('routeModal'));
    },

    /**
     * Abre el modal y carga la ruta
     */
    openRoute(direction) {
        this.modalElement.show();
        
        // Esperar a que el modal se visualice completamente
        setTimeout(() => {
            this.initializeMap();
            this.getUserLocation().then(userLoc => {
                if (userLoc) {
                    this.geocodeAddress(direction, userLoc);
                }
            });
        }, 500);
    },

    /**
     * Obtiene la ubicación actual del usuario
     */
    getUserLocation() {
        return new Promise((resolve) => {
            if (this.userLocation) {
                resolve(this.userLocation);
                return;
            }

            if (!navigator.geolocation) {
                console.error('Geolocalización no soportada en este navegador');
                this.showError('Tu navegador no soporta geolocalización');
                resolve(null);
                return;
            }

            // Mostrar estado de carga
            const loadingElement = document.getElementById('loadingRoute');
            const errorElement = document.getElementById('errorRoute');
            
            if (loadingElement) loadingElement.style.display = 'block';
            if (errorElement) errorElement.style.display = 'none';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    console.log('Ubicación del usuario:', this.userLocation);
                    const loadingEl = document.getElementById('loadingRoute');
                    if (loadingEl) loadingEl.style.display = 'none';
                    resolve(this.userLocation);
                },
                (error) => {
                    console.error('Error al obtener ubicación:', error);
                    let errorMsg = 'No se pudo obtener tu ubicación. ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg += 'Por favor, habilita los permisos de ubicación.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg += 'La información de ubicación no está disponible.';
                            break;
                        case error.TIMEOUT:
                            errorMsg += 'La solicitud de ubicación expiró.';
                            break;
                    }
                    
                    this.showError(errorMsg);
                    const loadingEl = document.getElementById('loadingRoute');
                    if (loadingEl) loadingEl.style.display = 'none';
                    resolve(null);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    },

    /**
     * Geocodifica una dirección a coordenadas usando Nominatim
     */
    geocodeAddress(address, userLocation) {
        const loadingEl = document.getElementById('loadingRoute');
        const errorEl = document.getElementById('errorRoute');
        if (loadingEl) loadingEl.style.display = 'block';
        if (errorEl) errorEl.style.display = 'none';

        // Usar Nominatim de OpenStreetMap para geocodificación
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const loadingEl = document.getElementById('loadingRoute');
                if (loadingEl) loadingEl.style.display = 'none';

                if (!data || data.length === 0) {
                    this.showError('No se encontró la ubicación: ' + address);
                    return;
                }

                const destination = {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon),
                    name: address
                };

                this.drawMapWithRoute(userLocation, destination);
            })
            .catch(error => {
                const loadingEl = document.getElementById('loadingRoute');
                if (loadingEl) loadingEl.style.display = 'none';
                console.error('Error geocodificando dirección:', error);
                this.showError('Error al procesar la ubicación. Intenta nuevamente.');
            });
    },

    /**
     * Inicializa el mapa Leaflet
     */
    initializeMap() {
        if (this.map) {
            this.map.invalidateSize();
            return;
        }

        const mapContainer = document.getElementById('leafletMap');
        if (!mapContainer) return;

        this.map = L.map('leafletMap', {
            center: [-12.0466, -77.0369], // Lima, Perú como centro default
            zoom: 13,
            scrollWheelZoom: true
        });

        // Agregar capa de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Crear grupos de capas
        this.routeLayer = L.featureGroup().addTo(this.map);
    },

    /**
     * Dibuja el mapa con la ruta
     */
    drawMapWithRoute(userLocation, destination) {
        // Limpiar capas anteriores
        this.routeLayer.clearLayers();
        if (this.userMarker) this.map.removeLayer(this.userMarker);
        if (this.destinationMarker) this.map.removeLayer(this.destinationMarker);

        // Marcador de ubicación del usuario
        this.userMarker = L.marker([userLocation.lat, userLocation.lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            }),
            title: 'Tu ubicación actual'
        }).bindPopup('<b>Tu Ubicación Actual</b><br/>' + 
                     userLocation.lat.toFixed(4) + ', ' + 
                     userLocation.lng.toFixed(4))
         .addTo(this.map);

        // Marcador de destino
        this.destinationMarker = L.marker([destination.lat, destination.lng], {
            icon: L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            }),
            title: 'Ubicación de destino'
        }).bindPopup('<b>Ubicación de Destino</b><br/>' + destination.name)
         .addTo(this.map);

        // Obtener ruta usando OSRM
        this.getRoute(userLocation, destination);

        // Ajustar zoom y vista
        const group = new L.featureGroup([this.userMarker, this.destinationMarker]);
        this.map.fitBounds(group.getBounds().pad(0.1));
    },

    /**
     * Obtiene la ruta usando OSRM (Open Source Routing Machine)
     */
    getRoute(start, end) {
        const loadingEl = document.getElementById('loadingRoute');
        if (loadingEl) loadingEl.style.display = 'block';
        
        // URL de OSRM
        const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${start.lng},${start.lat};${end.lng},${end.lat}?overview=full&geometries=geojson&steps=true&annotations=distance,duration`;

        fetch(osrmUrl)
            .then(response => response.json())
            .then(data => {
                const loadingEl = document.getElementById('loadingRoute');
                if (loadingEl) loadingEl.style.display = 'none';

                if (!data.routes || data.routes.length === 0) {
                    this.showError('No se pudo calcular la ruta');
                    return;
                }

                const route = data.routes[0];
                
                // Dibujar la ruta en el mapa
                const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                
                const polyline = L.polyline(coordinates, {
                    color: '#3b82f6',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '5, 5'
                }).addTo(this.map);

                this.routeLayer.addLayer(polyline);

                // Mostrar información de la ruta
                const distance = (route.distance / 1000).toFixed(2); // convertir a km
                const duration = Math.round(route.duration / 60); // convertir a minutos

                const distEl = document.getElementById('routeDistance');
                const durEl = document.getElementById('routeDuration');
                const infoEl = document.getElementById('routeInfo');
                
                if (distEl) distEl.textContent = distance + ' km';
                if (durEl) durEl.textContent = duration + ' min';
                if (infoEl) infoEl.style.display = 'block';

                // Actualizar link de Google Maps
                const startCoords = `${start.lat},${start.lng}`;
                const endCoords = `${end.lat},${end.lng}`;
                const mapsBtn = document.getElementById('openGoogleMaps');
                if (mapsBtn) {
                    mapsBtn.href = `https://www.google.com/maps/dir/${startCoords}/${endCoords}`;
                    mapsBtn.style.display = 'inline-block';
                }

                // Animar la ruta
                this.animateRoute(coordinates);
            })
            .catch(error => {
                const loadingEl = document.getElementById('loadingRoute');
                if (loadingEl) loadingEl.style.display = 'none';
                console.error('Error obteniendo ruta:', error);
                this.showError('Error al calcular la ruta. Intenta nuevamente.');
            });
    },

    /**
     * Anima la ruta en el mapa
     */
    animateRoute(coordinates) {
        let index = 0;
        const animationPoints = [];
        
        // Reducir puntos para animación más suave
        const step = Math.ceil(coordinates.length / 50);
        for (let i = 0; i < coordinates.length; i += step) {
            animationPoints.push(coordinates[i]);
        }

        const animateStep = () => {
            if (index < animationPoints.length) {
                index++;
                setTimeout(animateStep, 20);
            }
        };

        animateStep();
    },

    /**
     * Muestra un mensaje de error
     */
    showError(message) {
        const errorElement = document.getElementById('errorRoute');
        const errorMessage = document.getElementById('errorMessage');
        
        if (errorMessage) errorMessage.textContent = message;
        if (errorElement) errorElement.style.display = 'block';
    }
};

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    LeafletRoutes.initModal();
});