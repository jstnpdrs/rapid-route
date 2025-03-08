<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Separator } from '@/components/ui/separator';

import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw/dist/leaflet.draw.css';
import 'leaflet-draw';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/users' },
  { title: 'New User', href: '/users/new' }
];

const latitude = ref<number | null>(null);
const longitude = ref<number | null>(null);
const map = ref<any>(null);
const currentPolygon = ref<number[][]>([]);
const polygons = ref<number[][][]>([]);
const drawnPolygons = ref<any>(L.featureGroup());
const isLoading = ref(false);
const routeLine = ref<any>(null);
const startMarker = ref<any>(null);
const endMarker = ref<any>(null);

navigator.geolocation.getCurrentPosition(position => {
  latitude.value = position.coords.latitude;
  longitude.value = position.coords.longitude;
});

const initializeMap = () => {
  map.value = L.map('map').setView([11.708447, 122.367396], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map.value);

  map.value.addLayer(drawnPolygons.value);
  addPolygonDrawTool();
  handlePolygonCreated();
};

const addPolygonDrawTool = () => {
  map.value.addControl(new L.Control.Draw({
    draw: {
      polygon: true,
      polyline: false,
      rectangle: false,
      circle: false,
      marker: false,
      circlemarker: false
    },
    edit: {
      featureGroup: drawnPolygons.value,
      remove: false
    }
  }));
};

const handlePolygonCreated = () => {
  map.value.on(L.Draw.Event.CREATED, (e: any) => {
    const layer = e.layer;
    drawnPolygons.value.addLayer(layer);
    const latLngs = getLatLngs(layer);
    polygons.value.push([latLngs]);
    bindPopupToLayer(layer, polygons.value.length - 1);
  });
};

const getLatLngs = (layer: any) => {
  const latLngs = layer.getLatLngs()[0].map((latlng: L.LatLng) => [latlng.lng, latlng.lat]);
  if (latLngs[0][0] !== latLngs[latLngs.length - 1][0] || latLngs[0][1] !== latLngs[latLngs.length - 1][1]) {
    latLngs.push(latLngs[0]);
  }
  return latLngs;
};

const bindPopupToLayer = (layer: any, index: number) => {
  layer.bindPopup(`
    <div style="text-align: center;">
      <button onclick="window.deletePolygon(${index})"
        style="background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">
        🗑️ Delete Polygon
      </button>
    </div>
  `).openPopup();
};

window.deletePolygon = (index: number) => {
  const layerToRemove = drawnPolygons.value.getLayers()[index];
  if (layerToRemove) {
    map.value.removeLayer(layerToRemove);
    drawnPolygons.value.removeLayer(layerToRemove);
    polygons.value.splice(index, 1);
    rebindPopups();
  }
};

const rebindPopups = () => {
  drawnPolygons.value.eachLayer((layer, idx) => {
    bindPopupToLayer(layer, idx);
  });
};

const fetchRoute = async () => {
  isLoading.value = true;
  const apiKey = '5b3ce3597851110001cf624822687a09a4ba468d929df60948e1cf92';
  const start = [122.36805081367494, 11.708814765121971];
  const end = [122.36387729644777, 11.708499597357623];

  clearRouteAndMarkers();
  addMarkers(start, end);

  const body: any = {
    coordinates: [start, end]
  };

  if (polygons.value.length > 0) {
    body.options = {
      avoid_polygons: {
        type: 'MultiPolygon',
        coordinates: polygons.value
      }
    };
  }

  try {
    const response = await fetchRouteFromAPI(apiKey, body);
    const data = await response.json();
    const decodedCoordinates = decodePolyline(data.routes[0].geometry);
    drawRoute(decodedCoordinates);
  } catch (error) {
    console.error('Error:', error);
    alert('Failed to fetch route.');
  } finally {
    isLoading.value = false;
  }
};

const fetchRouteFromAPI = (apiKey: string, body: any) => {
  const url = `https://api.openrouteservice.org/v2/directions/driving-car`;
  return fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: apiKey
    },
    body: JSON.stringify(body)
  });
};

const addMarkers = (start: number[], end: number[]) => {
  startMarker.value = L.circleMarker([start[1], start[0]], {
    color: 'green'
  }).addTo(map.value).bindPopup('Starting Point');

  endMarker.value = L.circleMarker([end[1], end[0]], {
    color: 'red'
  }).addTo(map.value).bindPopup('End Point');
};

const drawRoute = (coordinates: [number, number][]) => {
  routeLine.value = L.polyline(coordinates, { color: 'blue' }).addTo(map.value);
  map.value.fitBounds(routeLine.value.getBounds());
};

const clearRouteAndMarkers = () => {
  if (routeLine.value) map.value.removeLayer(routeLine.value);
  if (startMarker.value) map.value.removeLayer(startMarker.value);
  if (endMarker.value) map.value.removeLayer(endMarker.value);
};

const decodePolyline = (polyline: string): [number, number][] => {
  let index = 0, lat = 0, lng = 0;
  const coordinates: [number, number][] = [];

  while (index < polyline.length) {
    let result = 0, shift = 0, byte = 0;

    do {
      byte = polyline.charCodeAt(index++) - 63;
      result |= (byte & 0x1f) << shift;
      shift += 5;
    } while (byte >= 0x20);

    const deltaLat = (result & 1) ? ~(result >> 1) : (result >> 1);
    lat += deltaLat;

    result = shift = 0;
    do {
      byte = polyline.charCodeAt(index++) - 63;
      result |= (byte & 0x1f) << shift;
      shift += 5;
    } while (byte >= 0x20);

    const deltaLng = (result & 1) ? ~(result >> 1) : (result >> 1);
    lng += deltaLng;

    coordinates.push([lat / 1e5, lng / 1e5]);
  }

  return coordinates;
};

onMounted(initializeMap);
</script>

<template>
  <Head title="Test" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-10">
      <div class="flex space-x-4 items-center">
        <ChevronLeft @click="router.visit(route('user.index'))"
          class="w-6 h-6 pr-0.5 inline-block hover:bg-slate-900 hover:text-muted rounded-md cursor-pointer" />
        <h3 class="text-lg font-medium">Update user</h3>
      </div>
      <Separator />
      <div id="map" style="width: 100%; height: 500px;"></div>
      <button @click="fetchRoute" :disabled="isLoading">
        {{ isLoading ? 'Generating Route...' : 'Generate Route' }}
      </button>
    </div>
  </AppLayout>
</template>