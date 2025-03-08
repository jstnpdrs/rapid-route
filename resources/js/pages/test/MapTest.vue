<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Separator } from '@/components/ui/separator';

import { Head, router } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Breadcrumb setup
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/users' },
  { title: 'New User', href: '/users/new' }
];

// Map and geolocation setup
const latitude = ref<number | null>(null);
const longitude = ref<number | null>(null);

navigator.geolocation.getCurrentPosition(position => {
  latitude.value = position.coords.latitude;
  longitude.value = position.coords.longitude;
});

const map = ref<any>(null);
const currentPolygon = ref<number[][]>([]);
const polygons = ref<number[][][]>([]);
const drawnPolygons = ref<any[]>([]);

// Helper function to add points for polygons
const addPoint = (e: L.LeafletMouseEvent) => {
  const { lat, lng } = e.latlng;
  currentPolygon.value.push([lng, lat]);  // Store as [lon, lat]
  // L.marker(e.latlng).addTo(map.value);
  L.circleMarker(e.latlng,{radius:4}).addTo(map.value);
};

// Polygon completion
const completePolygon = () => {
  if (currentPolygon.value.length < 3) {
    alert('A polygon must have at least 3 points.');
    return;
  }
  currentPolygon.value.push(currentPolygon.value[0]);
  polygons.value.push([[...currentPolygon.value]]);

  const latLngPolygon = currentPolygon.value.map(([lon, lat]) => [lat, lon]);
  const polygonLayer = L.polygon(latLngPolygon, { color: 'red' }).bindPopup("<button onClick='console.log('asdasd')'>click me!</button>").addTo(map.value);
  drawnPolygons.value.push(polygonLayer);

  currentPolygon.value = [];
};

// Route fetching with polygon avoidance
const fetchRoute = async () => {
  const apiKey = '5b3ce3597851110001cf624822687a09a4ba468d929df60948e1cf92';
  const start = [122.36805081367494, 11.708814765121971]; // [lon, lat]
  const end = [122.36387729644777, 11.708499597357623];   // [lon, lat]
  // return console.log(polygons.value);
  
  const avoidPolygon = {
    type: 'MultiPolygon',
    coordinates: polygons.value
  };

  const url = `https://api.openrouteservice.org/v2/directions/driving-car`;
  const body = JSON.stringify({
    coordinates: [start, end],
    options: { avoid_polygons: avoidPolygon }
  });

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: apiKey
      },
      body
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    if (!data.routes || data.routes.length === 0) {
      throw new Error('No route found');
    }

    // Decode the polyline returned by ORS
    const encodedPolyline = data.routes[0].geometry;
    const decodedCoordinates = decodePolyline(encodedPolyline);

    // Draw the route on the map
    const routeLine = L.polyline(decodedCoordinates, { color: 'blue' }).addTo(map.value);
    map.value.fitBounds(routeLine.getBounds());
  } catch (error) {
    console.error('Error fetching route:', error);
  }
};

// Simple polyline decoder (no external dependencies)
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

// Save polygons (optional, adjust to your backend API if needed)
const savePolygons = async () => {
  console.log('Saving polygons:', polygons.value);
};

// Initialize map
onMounted(() => {
  map.value = L.map('map').setView([11.708447, 122.367396], 16);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map.value);
  map.value.on('click', addPoint);
});

</script>

<template>

  <Head title="Test" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-10">
      <div class="flex space-x-4 items-center">
        <ChevronLeft @click="router.visit(route('user.index'))"
          class="w-6 h-6 pr-0.5 inline-block hover:bg-slate-900 hover:text-muted rounded-md cursor-pointer" />
        <h3 class="text-lg font-medium">
          Update user
        </h3>
      </div>
      <Separator />
      map test test test
      <div>
        {{ latitude }} {{ longitude }}
      </div>

      <div>

        <div id="map" style="width: 100%; height: 500px;"></div>
        <button @click="completePolygon" :disabled="!currentPolygon.length">
          Complete Polygon
        </button>
        <button @click="savePolygons" :disabled="!polygons.length">
          Save Polygons
        </button>
        <button @click="fetchRoute" :disabled="!polygons.length">
          Generate Route
        </button>
      </div>
    </div>
  </AppLayout>
</template>
