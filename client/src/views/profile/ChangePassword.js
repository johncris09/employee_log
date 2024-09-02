import React, { useState, useEffect } from 'react'
import './../../assets/css/react-paginate.css'
import { CButton, CCol, CForm, CFormInput, CFormLabel, CFormText, CInputGroup } from '@coreui/react'
import { useFormik } from 'formik'
import { ToastContainer, toast } from 'react-toastify'
import { jwtDecode } from 'jwt-decode'
import { useNavigate } from 'react-router-dom'
import {
  DefaultLoading,
  RequiredFieldNote,
  api,
  requiredField,
  toSentenceCase,
} from 'src/components/SystemConfiguration'
import * as Yup from 'yup'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons'

const ChangePassword = () => {
  const navigate = useNavigate()
  const [validated, setValidated] = useState(true)
  const [operationLoading, setOperationLoading] = useState(false)
  const [editId, setEditId] = useState('')
  const [togglePassword, setTogglePassword] = useState(true)

  useEffect(() => {
    const userInfo = jwtDecode(localStorage.getItem('employeeLogsToken'))
    setEditId(userInfo.id)
    setValidated(false)
  }, [])

  const updatePasswordFormValidationSchema = Yup.object().shape({
    password: Yup.string()
      .required('Password is required')
      .min(7, 'Too Short!')
      .max(12, 'Too Long!')
      .matches(
        /^(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/,
        'Password must have at least 1 uppercase letter, 1 symbol, and be at least 8 characters',
      ),
  })
  const form = useFormik({
    initialValues: {
      password: '',
    },
    validationSchema: updatePasswordFormValidationSchema,
    onSubmit: async (values) => {
      setOperationLoading(true)
      await api
        .put('user/change_password/' + editId, values)
        .then((response) => {
          toast.success(response.data.message)
          setValidated(false)
          localStorage.removeItem('employeeLogsToken')
          navigate('/login', { replace: true })
        })
        .catch((error) => {
          console.info(error)
          // toast.error(handleError(error))
        })
        .finally(() => {
          setOperationLoading(false)
        })
    },
  })

  const handleInputChange = (e) => {
    form.handleChange(e)
    const { name, value, type } = e.target
    if (type === 'text' && name !== 'password') {
      form.setFieldValue(name, toSentenceCase(value))
    } else {
      form.setFieldValue(name, value)
    }
  }

  return (
    <>
      <ToastContainer />
      <RequiredFieldNote />
      <CForm
        className="row g-3 needs-validation mt-4"
        noValidate
        validated={validated}
        onSubmit={form.handleSubmit}
        style={{ position: 'relative' }}
      >
        <CCol md={12}>
          <CFormLabel>{requiredField('Password')}</CFormLabel>
          <CInputGroup className="mb-3">
            <CFormInput
              type={togglePassword ? 'password' : 'text'}
              name="password"
              onChange={handleInputChange}
              value={form.values.password}
              required
              placeholder="Password"
            />
            <CButton
              onClick={() => {
                setTogglePassword((prevShowPassword) => !prevShowPassword)
              }}
              type="button"
              color="secondary"
              variant="outline"
            >
              <FontAwesomeIcon icon={togglePassword ? faEye : faEyeSlash} />
            </CButton>
          </CInputGroup>
          {form.touched.password && form.errors.password && (
            <CFormText className="text-danger">{form.errors.password}</CFormText>
          )}
        </CCol>

        <hr />
        <CCol xs={12}>
          <CButton color="primary" type="submit" className="float-end">
            Update Password
          </CButton>
        </CCol>
      </CForm>
      {operationLoading && <DefaultLoading />}
    </>
  )
}

export default ChangePassword
