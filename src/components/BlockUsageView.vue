<template>
  <k-panel-inside>
    <k-header>Block Usage</k-header>

    <k-text>Searching blocks of the <code>{{ fieldName }}</code> field in all pages.</k-text>

    <k-select-field
        :options="options"
        v-model="selected"
        @input="search"
        label="Block Type"
        style="margin-top: 1rem;" />

    <k-item
        v-if="error"
        :image="{ icon: 'bug' }"
        :text="error"
        style="margin-top: 1rem"
    />

    <k-empty v-if="selected && results.length === 0" style="margin-top: 1rem;">
      No pages found.
    </k-empty>

    <k-grid style="--columns: 4; grid-template-columns: 1fr 1fr 100px 100px; gap: 0.5rem; align-items: center; margin-top: 1rem;">
      <p>
        <strong>Page title</strong>
      </p>
      <p>
        <strong>Page</strong>
      </p>
      <p>
        <strong>Occurrences</strong>
      </p>
      <p style="text-align: center;">
        <strong>Panel link</strong>
      </p>

      <template v-for="result in results"
                :key="`${result.pageId}-${result.field}`">
        <h3>{{ result.title }}</h3>

        <p>
          {{ result.lang }}/{{ result.pageId }}
        </p>

        <p>
          {{ result.count }}
        </p>

        <k-button
            theme="positive"
            icon="open"
            :link="result.panelUrl"
            target="_blank"
        >
          link
        </k-button>
      </template>


    </k-grid>
  </k-panel-inside>
</template>

<script>
export default {

  props: {
    fieldName: {
      type: String,
      default: "blocks"
    }
  },

  data() {
    return {
      selected: null,
      options: [],
      results: [],
      error: null
    };
  },

  async created() {
    const blocks = await this.$api.get(
        "block-finder/block-types"
    ).catch(error => {
      this.error = error.message;
    });

    this.options = blocks.map(type => ({
      value: type,
      text: type
    }));
  },

  methods: {

    async search() {
      if (!this.selected) {
        this.results = [];
        return;
      }

      this.results = await this.$api.get(
          `block-finder/search?type=${this.selected}`
      ).catch(error => {
        this.error = error.message;
        this.results = [];
      });
    }
  }
};
</script>