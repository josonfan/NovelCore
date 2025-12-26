<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建渠道' : '编辑渠道'"
    width="720px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form
      :model="form"
      label-width="120px"
      class="form"
    >
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="渠道名称">
            <el-input
              :model-value="form.name"
              @update:model-value="updateForm('name', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="状态">
            <el-switch
              :model-value="form.status"
              :active-value="1"
              :inactive-value="0"
              @update:model-value="updateForm('status', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-form-item label="USDT">
            <el-switch
              :model-value="form.is_usdt"
              :active-value="1"
              :inactive-value="2"
              @update:model-value="updateForm('is_usdt', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="默认">
            <el-switch
              :model-value="form.is_default"
              :active-value="1"
              :inactive-value="2"
              @update:model-value="updateForm('is_default', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="Web">
            <el-switch
              :model-value="form.is_web"
              :active-value="1"
              :inactive-value="2"
              @update:model-value="updateForm('is_web', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="8">
          <el-form-item label="PC禁用">
            <el-switch
              :model-value="form.not_pc"
              :active-value="1"
              :inactive-value="2"
              @update:model-value="updateForm('not_pc', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="8">
          <el-form-item label="排序">
            <el-input-number
              :model-value="form.sort"
              :min="0"
              @update:model-value="updateForm('sort', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item label="支付地址">
        <el-input
          :model-value="form.pay_url"
          @update:model-value="updateForm('pay_url', $event)"
        />
      </el-form-item>
      <el-form-item label="补单地址">
        <el-input
          :model-value="form.sup_order_url"
          @update:model-value="updateForm('sup_order_url', $event)"
        />
      </el-form-item>
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="图标标识">
            <el-input
              :model-value="form.icon_iden"
              @update:model-value="updateForm('icon_iden', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="银行编码">
            <el-input
              :model-value="form.pay_bankcode"
              @update:model-value="updateForm('pay_bankcode', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="限额">
            <el-input
              :model-value="form.limit_price"
              @update:model-value="updateForm('limit_price', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="周期额">
            <el-input
              :model-value="form.cycle_price"
              @update:model-value="updateForm('cycle_price', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-row :gutter="20">
        <el-col :span="12">
          <el-form-item label="支付渠道ID">
            <el-input
              :model-value="form.pay_id"
              @update:model-value="updateForm('pay_id', $event)"
            />
          </el-form-item>
        </el-col>
        <el-col :span="12">
          <el-form-item label="SKey">
            <el-input
              :model-value="form.skey"
              type="password"
              show-password
              @update:model-value="updateForm('skey', $event)"
            />
          </el-form-item>
        </el-col>
      </el-row>
      <el-form-item label="MD5 Key">
        <el-input
          :model-value="form.md5_key"
          type="password"
          show-password
          @update:model-value="updateForm('md5_key', $event)"
        />
      </el-form-item>
      <el-form-item label="支付规则">
        <el-input
          type="textarea"
          :rows="2"
          :model-value="form.pay_rules"
          @update:model-value="updateForm('pay_rules', $event)"
        />
      </el-form-item>
      <el-form-item label="备注">
        <el-input
          type="textarea"
          :rows="2"
          :model-value="form.remarks"
          @update:model-value="updateForm('remarks', $event)"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">
        取消
      </el-button>
      <el-button
        type="primary"
        @click="$emit('save')"
      >
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { PaymentChannelFormData } from '../../composables/usePaymentChannelForm'

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  form: PaymentChannelFormData
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:form': [value: PaymentChannelFormData]
  'save': []
}>()

function updateForm<K extends keyof PaymentChannelFormData>(
  key: K,
  value: PaymentChannelFormData[K]
) {
  emit('update:form', { ...props.form, [key]: value })
}
</script>
