<template>
  <div id="footer" class="acea-row row-middle">
    <router-link
      :to="item.url"
      class="item"
      :class="{ on: item.url === $route.path }"
      v-for="(item, index) in footerList"
      :key="index"
    >
      <div
        class="iconfont"
        :class="item.icon1 + ' ' + (item.url === $route.path ? item.icon2 : '')"
      ></div>
      <div>{{ item.name }}</div>
    </router-link>
  </div>
</template>
<script>
export default {
  name: "Footer",
  props: {},
  data: function() {
    return {
      footerList: [
        {
          name: "首页",
          icon1: "icon-shouye3",
          icon2: "icon-shouye2",
          url: "/"
        },
        {
          name: "拼团",
          icon1: "icon-fenlei1",
          icon2: "icon-fenlei",
          url: "/category"
        },
        {
          name: "订单",
          icon1: "icon-2",
          icon2: "icon-dingdan",
          url: "/order/list"
        },
        {
          name: "我的",
          icon1: "icon-geren1",
          icon2: "icon-geren",
          url: "/user"
        }
      ]
    };
  },
  methods: {}
};
</script>
