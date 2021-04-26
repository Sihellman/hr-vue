<template>
  <div class="competency">
    <form action="#">
      <div class="competency-form">
        <p>competency name: <input type="text" v-model="newName" required /></p>
        <br />
        <p class="textarea-field">
          <label for="textarea">competency description: </label>
          <textarea rows="4" cols="100" v-model="newDescr" required></textarea>
        </p>
      </div>
      <br />
      <button v-on:click="createComp()">Create</button>
    </form>
    <br />
    <table>
      <thead>
        <th>name</th>
        <th>description</th>
        <th>&nbsp;&nbsp;&nbsp;&nbsp;</th>
      </thead>
      <tr v-for="comp in comps">
        <td>{{ comp.name }}</td>
        <td>{{ comp.descr }}</td>
        <td>
          <button v-on:click="showForm(comp)">Edit</button>
        </td>
        <dialog id="form-edit">
          <form method="dialog">
            <div class="competency-form">
              <p>
                competency name:
                <input type="text" v-model="currentComp.name" required />
              </p>
              <br />
              <p class="textarea-field">
                <label for="textarea">competency description: </label>
                <textarea
                  rows="4"
                  cols="100"
                  v-model="currentComp.descr"
                  required
                s></textarea>
              </p>
            </div>
            <br />
            <button class="btn" v-on:click="updateComp(currentComp)">
              Update
            </button>
            <button class="btn" v-on:click="destroyComp(currentComp)">
              Destroy
            </button>
            <button class="btn">Close</button>
          </form>
        </dialog>
      </tr>
    </table>
  </div>
</template>

<style></style>

<script>
var axios = require("axios");

export default {
  data: function() {
    return {
      url: "http://localhost/project2/data_p2.php",
      comps: [],
      currentComp: {},
      newName: "",
      newDescr: "",
    };
  },
  created: function() {
    this.getComp();
  },
  methods: {
    getComp: function() {
      
        const params = new URLSearchParams();
        params.append("mode", "all");
        params.append("type", "competency");

        axios
          .post(this.url, params)
          .then((response) => {
            //console.log("Success creating recipe", response.data);
            for (let i = 0; i < response.data.length; i++) {
              this.comps.push(response.data[i]);
              console.log(response.data[i]);
            }
          });
    
    },
    showForm: function(comp) {
      console.log(comp);
      this.currentComp = comp;
      document.querySelector("#form-edit").showModal();
    },
    createComp: function() {
      console.log("Create the recipe...");
      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "competency");
      params.append("comp", this.newName);
      params.append("descr", this.newDescr);

      axios
        .post(this.url, params)
        .then((response) => {
          console.log("Success creating recipe", response.data);
          this.comps.push(response.data[0]);
        });
    },
    updateComp: function(comp) {
      const params = new URLSearchParams();
      params.append("mode", "save");
      params.append("type", "competency");
      params.append("competency_id", comp.competency_id);
      params.append("comp", comp.name);
      params.append("descr", comp.descr);

      axios
        .post(this.url, params)
        .then((response) => {
          console.log("Success updating recipe", response.data);
          for (let i = 0; i < this.comps.length; i++) {
            if (
              response.data[0].competency_id === this.comps[i].competency_id
            ) {
              this.comps.splice(i, 1, response.data[0]);
            }
          }
        });
    },
    destroyComp: function(comp) {
      const params = new URLSearchParams();
      params.append("mode", "delete");
      params.append("type", "competency");
      params.append("competency_id", comp.competency_id);

      axios
        .post(this.url, params)
        .then((response) => {
          for (let i = 0; i < this.comps.length; i++) {
            if (parseInt(comp.competency_id) === this.comps[i].competency_id) {
              this.comps.splice(i, 1);
            }
          }
        });
    },
  },
};
</script>
