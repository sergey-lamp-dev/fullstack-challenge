<script lang="ts">
import { defineComponent, ref, onMounted } from "vue";
import { useUserStore } from "@/stores/userStore";
import { useWeatherStore } from "@/stores/weatherStore";
import type { User } from "@/types/user.interface";
import type { WeatherData } from "@/types/weather.interface";
import Dialog from "primevue/dialog";
import io from "socket.io-client";

export default defineComponent({
  components: {
    Dialog,
  },
  setup() {
    // Set up a websocket connection to the server
    // const socket = io("http://localhost:3000");

    const userStore = useUserStore();
    const weatherStore = useWeatherStore();

    const loadingUsers = ref<boolean>(false);
    const loadingWeather = ref<boolean>(false);
    const users = ref<User[]>([]);
    const showModal = ref<boolean>(false);
    const selectedUser = ref<User | null>(null);
    const weatherData = ref<WeatherData | null>(null);

    // Listen for weather updates for this user
    // socket.on(
    //   `user-weather`,
    //   (data: { weather: WeatherData; userId: number }) => {
    //     const user = users.value.find((u) => u.id === data.userId);
    //     if (user) {
    //       user.weather = data.weather;
    //     }
    //   }
    // );

    const showDetails = async (user: User) => {
      selectedUser.value = user;
      showModal.value = true;

      if (selectedUser.value) {
        loadingWeather.value = true;

        try {
          await weatherStore.fetchWeatherData(user.id);
          weatherData.value = weatherStore.weatherData;
        } catch (error) {
          console.error(error);
        }

        loadingWeather.value = false;
      }
    };

    const closeModal = () => {
      showModal.value = false;
      selectedUser.value = null;
    };

    onMounted(async () => {
      loadingUsers.value = true;
      await userStore.fetchUsers();
      users.value = userStore.users;
      loadingUsers.value = false;
    });

    return {
      loadingUsers,
      loadingWeather,
      weatherData,
      users,
      showDetails,
      showModal,
      selectedUser,
      closeModal,
    };
  },
});
</script>

<template>
  <div>
    <div class="p-d-flex p-jc-center">
      <div
        class="p-mt-6 p-grid p-justify-center p-align-center"
        style="width: 100%"
      >
        <div
          v-if="!loadingUsers && users"
          class="p-col-12 p-md-6 p-lg-4"
          v-for="user in users"
          :key="user.id"
        >
          <div class="p-shadow-3 p-p-3 p-mb-3 p-d-flex p-flex-column">
            <div class="p-text-center">
              <h3>{{ user.name }}</h3>
              <p class="p-mt-3">{{ user.email }}</p>
            </div>
            <div class="p-text-center">
              <h4>Current Weather:</h4>
              <p>
                {{ user?.weather }}
              </p>
            </div>
            <div class="p-mt-4 p-text-center">
              <h5>Current Location:</h5>
              <p class="p-mt-2">Latitude: {{ user.latitude }}</p>
              <p>Longitude: {{ user.longitude }}</p>
            </div>
            <div class="p-mt-4 p-d-flex p-jc-center">
              <button
                class="p-button p-button-success"
                @click="showDetails(user)"
              >
                Detailed weather report
              </button>
            </div>
          </div>
        </div>
        <div v-else-if="loadingUsers">Loading users data...</div>
        <div v-else>No users data available.</div>
      </div>
    </div>

    <Dialog
      v-model:visible="showModal"
      :header="selectedUser?.name + ' Weather'"
      :modal="true"
      @hide="closeModal"
    >
      <div v-if="!loadingWeather && weatherData">
        <p>Temperature: {{ weatherData.main.temp }} &#8451;</p>
        <p>Feels like: {{ weatherData.main.feels_like }} &#8451;</p>
        <p>Description: {{ weatherData.weather[0].description }}</p>
      </div>
      <div v-else-if="loadingWeather">Loading weather data...</div>
      <div v-else>No weather data available.</div>
    </Dialog>
  </div>
</template>
