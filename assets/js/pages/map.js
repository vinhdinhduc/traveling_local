// Page JS: map

document.addEventListener("DOMContentLoaded", function () {
  const mapElement = document.getElementById("tourism-map");
  if (!mapElement || typeof window.L === "undefined") {
    return;
  }

  const markersData = Array.isArray(window.tourismMarkers)
    ? window.tourismMarkers.filter(function (item) {
        return (
          item &&
          typeof item.lat === "number" &&
          typeof item.lng === "number" &&
          Number.isFinite(item.lat) &&
          Number.isFinite(item.lng)
        );
      })
    : [];

  const defaultCenter = [20.744, 104.81];
  const map = L.map("tourism-map", {
    scrollWheelZoom: true,
    zoomControl: true,
  }).setView(defaultCenter, 12);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
  }).addTo(map);

  const markerStore = [];
  const bounds = L.latLngBounds([]);

  function createIcon(type) {
    const color = type === "homestay" ? "#1e88e5" : "#e53935";
    return L.divIcon({
      className: "custom-map-marker",
      html:
        '<span class="map-marker-dot" style="--marker-color:' +
        color +
        '"></span>',
      iconSize: [20, 20],
      iconAnchor: [10, 10],
      popupAnchor: [0, -12],
    });
  }

  function createPopupContent(item) {
    const wrapper = document.createElement("div");
    wrapper.className = "leaflet-popup-body";

    const title = document.createElement("h4");
    title.className = "leaflet-popup-title";
    title.textContent = item.name || "Địa điểm";

    const address = document.createElement("p");
    address.className = "leaflet-popup-address";
    address.textContent = item.address || "Chưa cập nhật địa chỉ";

    const link = document.createElement("a");
    link.className = "leaflet-popup-link";
    link.href = item.url || "#";
    link.textContent = "Xem chi tiết";

    wrapper.appendChild(title);
    wrapper.appendChild(address);
    wrapper.appendChild(link);

    return wrapper;
  }

  markersData.forEach(function (item) {
    const marker = L.marker([item.lat, item.lng], {
      title: item.name || "",
      icon: createIcon(item.type),
    });

    marker.bindPopup(createPopupContent(item));
    marker.addTo(map);

    markerStore.push({
      instance: marker,
      type: item.type,
      latlng: L.latLng(item.lat, item.lng),
    });

    bounds.extend([item.lat, item.lng]);
  });

  if (markerStore.length > 0) {
    map.fitBounds(bounds.pad(0.18));
  }

  const placeFilter = document.getElementById("filter-place");
  const homestayFilter = document.getElementById("filter-homestay");

  function applyFilters() {
    const showPlace = !placeFilter || placeFilter.checked;
    const showHomestay = !homestayFilter || homestayFilter.checked;
    const activeBounds = L.latLngBounds([]);
    let visibleCount = 0;

    markerStore.forEach(function (entry) {
      const isVisible =
        (entry.type === "place" && showPlace) ||
        (entry.type === "homestay" && showHomestay);

      if (isVisible) {
        if (!map.hasLayer(entry.instance)) {
          entry.instance.addTo(map);
        }
        activeBounds.extend(entry.latlng);
        visibleCount += 1;
      } else if (map.hasLayer(entry.instance)) {
        map.removeLayer(entry.instance);
      }
    });

    if (visibleCount > 0) {
      map.fitBounds(activeBounds.pad(0.18));
    }
  }

  if (placeFilter) {
    placeFilter.addEventListener("change", applyFilters);
  }

  if (homestayFilter) {
    homestayFilter.addEventListener("change", applyFilters);
  }
});
