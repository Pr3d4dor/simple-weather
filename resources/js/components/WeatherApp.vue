<template>
  <div class="text-white mb-8">
    <div class="places-input text-gray-800 md:w-128">
      <input type="search" id="address" class="form-control" placeholder="Choose a city..." />

      <p>Selected: <strong id="address-value">none</strong></p>
    </div>

    <div v-if="!selectedCity" class="md:w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-lg mt-8 flex justify-center items-center min-h-full">
        <p>Please select a city or use your location</p>
    </div>
    <div v-else-if="fetchingData" class="md:w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-lg mt-8 flex justify-center items-center min-h-full">
        <pulse-loader :loading="fetchingData" color="#FFFFFF"></pulse-loader>
    </div>
    <div v-else class="weather-container font-sans md:w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-lg mt-8">
      <div class="current-weather flex items-center justify-between px-6 py-8">
        <div class="flex flex-col md:flex-row items-center">
          <div>
            <div class="text-6xl font-semibold">{{ currentTemperature.actual }}°C</div>
            <div>Feels like {{ currentTemperature.feels }}°C</div>
          </div>
          <div class="md:mx-5">
            <div class="font-semibold">{{ currentTemperature.summary }}</div>
            <div>{{ location.name }}</div>
          </div>
        </div>
        <img v-bind:src="'https://openweathermap.org/img/w/' + currentTemperature.icon + '.png' "  />
      </div> <!-- end current-weather -->

      <div class="future-weather text-sm bg-gray-800 px-6 py-8 overflow-hidden">
        <div
          v-for="(day, index) in daily"
          :key="day.time"
          class="flex items-center"
          :class="{ 'mt-8' : index > 0 }"
        >
          <div class="w-1/6 text-lg text-center text-gray-200">{{ toDayOfMonth(day.dt) }}</div>
          <div class="w-1/6 text-lg text-center text-gray-200">{{ toDayOfWeek(day.dt) }}</div>
          <div class="w-3/6 px-4 flex justify-center items-center">
            <img v-bind:src="'https://openweathermap.org/img/w/' + day.weather[0].icon + '.png' "  />
            <div class="ml-3">{{ day.weather[0].main }}</div>
          </div>
          <div class="w-1/6 text-right">
            <div>{{ Math.round(day.temp.min) }}°C</div>
            <div>{{ Math.round(day.temp.max) }}°C</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
  components: {
    PulseLoader
  },
  mounted() {
    var placesAutocomplete = places({
      appId: process.env.MIX_ALGOLIA_APP_ID,
      apiKey: process.env.MIX_ALGOLIA_APP_KEY,
      container: document.querySelector('#address')
    })
    .configure({
      type: 'city',
      aroundLatLngViaIP: false,
    });

    var $address = document.querySelector('#address-value')

    placesAutocomplete.on('change', (e) => {
      this.selectedCity = true
      $address.textContent = e.suggestion.value

      this.location.name = `${e.suggestion.name}, ${e.suggestion.country}`
      this.location.lat = e.suggestion.latlng.lat
      this.location.lng = e.suggestion.latlng.lng
    });

    placesAutocomplete.on('clear', () => {
      this.location = {
        name: null,
        lat: null,
        lng: null,
      }
      this.selectedCity = false
    });
  },
  watch: {
    location: {
      handler(newValue, oldValue) {
        this.fetchData()
      },
      deep: true
    }
  },
  data() {
    return {
      currentTemperature: {
        actual: '',
        feels: '',
        summary: '',
        icon: '',
      },
      daily: [],
      location: {
        name: null,
        lat: null,
        lng: null,
      },
      fetchingData: true,
      selectedCity: false,
    }
  },
  methods: {
    fetchData() {
      this.fetchingData = true

      fetch(`/api/weather?lat=${this.location.lat}&lng=${this.location.lng}`)
        .then(response => response.json())
        .then(data => {
          this.currentTemperature.actual = Math.round(data.current.temp)
          this.currentTemperature.feels = Math.round(data.current.feels_like)
          this.currentTemperature.summary = data.current.weather[0].main
          this.currentTemperature.icon = data.current.weather[0].icon

          this.daily = data.daily

          this.fetchingData = false
        })
    },
    toKebabCase(stringToConvert) {
      return stringToConvert.split(' ').join('-')
    },
    toDayOfWeek(timestamp) {
      const newDate = new Date(timestamp*1000)
      const days = ['SUN','MON','TUE','WED','THU','FRI','SAT']

      return days[newDate.getDay()]
    },
    toDayOfMonth(timestamp) {
      const newDate = new Date(timestamp*1000)

      return String(newDate.getDate()).padStart(2, '0') + '/' + String(newDate.getMonth() + 1).padStart(2, '0');
    }
  }
}
</script>
