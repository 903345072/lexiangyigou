import {
  getAdminOrderList,
  setAdminOrderPrice,
  setAdminOrderRemark,
  setOfflinePay,
  setOrderRefund
} from "../../api/admin";
const app = getApp();
Component({
  properties: {
    orderInfo:{
      type:Object,
      value:null,
    },
    change:{
      type:Boolean,
      value:false,
    },
    status:{
      type:Number,
      value:0
    }
  },
  data: {
    remark: '',//备注信息
    price: '',//实际支付
    refund_price: ''//退款金额
  },
  attached: function () {
    console.log(this.data.status)
  },
  methods: {
     /**
    * 事件回调 
    */
    bindHideKeyboard: function (e) {
      this.setData({ remark: e.detail.value });
    },
     /**
    * 实际支付
    */
    bindPrice: function (e) {
      this.setData({ price: e.detail.value });
    },
     /**
    * 退款金额
    */
    bindRefundPrice: function(e) {
      this.setData({ refund_price: e.detail.value });
    },
     /**
    * 提交
    */
    save: function (e) {
      let type = e.currentTarget.dataset.type;
      this.savePrice(type);
    },
     /**
    * 拒绝退款
    */
    refuse: function (e) {
      let type = e.currentTarget.dataset.type;
      console.log(e)
      this.savePrice(type);
    },
    /**
    * 事件回调
    * 
    */
    savePrice: function (type) {
      let that = this,
        data = {},
        price = this.data.price,
        remark = this.data.remark,
        refund_price = this.data.refund_price;
        data.order_id = that.data.orderInfo.order_id;
      if (that.data.status == 0 && that.data.orderInfo.refund_status === 0) {
        if (!that.data.price) return app.Tips({ title: '请输入价格' });
        data.price = price;
        // 订单改价
        setAdminOrderPrice(data).then(
          function () {
            that.close();
            app.Tips("改价成功");
            that.triggerEvent('getIndex');
          },
          function () {
            that.close();
            app.Tips.error("改价失败");
          }
        );
      } else if (that.data.status == 0 && that.data.orderInfo.refund_status == 1) {
        if (type === '1' && !refund_price) return app.Tips({ title: '请输入退款金额' });
        data.price = refund_price;
        data.type = type;
         // 确认退款 拒绝退款
        setOrderRefund(data).then(
          res => {
            that.close();
            app.Tips(res.msg);
            that.triggerEvent('getIndex');
          },
          err => {
            that.close();
            app.Tips(err);
          }
        );
      } else {
        data.remark = remark;
        // 订单备注
        setAdminOrderRemark(data).then(
          res => {
            that.close();
            app.Tips(res.msg);
            that.triggerEvent('getIndex');
          },
          err => {
            that.close();
            app.Tips(err);
          }
        );
      }
    },
    close:function(){
      this.triggerEvent('onChangeFun',{action:'change'});
    }
  }
  
})