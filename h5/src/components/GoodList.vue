<template>
  <div class="goodList">
    <div
      @click="goDetails(item)"
      class="item acea-row row-between-wrapper"
      v-for="(item, index) in goodList"
      :key="index"
    >
      <div class="pictrue">
        <img :src="item.image" class="image" />
        <span
          class="pictrue_log pictrue_log_class"
          v-if="item.activity && item.activity.type === '1'"
          >秒杀</span
        >
        <span
          class="pictrue_log pictrue_log_class"
          v-if="item.activity && item.activity.type === '2'"
          >砍价</span
        >
        <span
          class="pictrue_log pictrue_log_class"
          v-if="item.activity && item.activity.type === '3'"
          >拼团</span
        >
      </div>
      <div class="underline">
        <div class="text">
          <div class="line1">{{ item.store_name }}</div>
          <div class="money font-color-red">
            ￥<span class="num">{{ item.price }}</span>
          </div>
          <div class="vip-money acea-row row-middle">
            <div class="vip" v-if="item.vip_price && item.vip_price > 0">
              ￥{{ item.vip_price || 0
              }}<img src="@assets/images/vip.png" class="image" />
            </div>
            <span class="num">已售{{ item.sales }}{{ item.unit_name }}</span>
          </div>
        </div>
      </div>
      <div
        class="iconfont icon-gouwuche cart-color acea-row row-center-wrapper"
      ></div>
    </div>
  </div>
</template>
<script>
import { goShopDetail } from "@libs/order";
export default {
  name: "GoodList",
  props: {
    goodList: {
      type: Array,
      default: () => []
    },
    isSort: {
      type: Boolean,
      default: true
    }
  },
  data: function() {
    return {};
  },
  methods: {
    // 商品详情跳转
    goDetails(item) {
      goShopDetail(item).then(() => {
        this.$router.push({ path: "/detail/" + item.id });
      });
    }
  }
};
</script>
