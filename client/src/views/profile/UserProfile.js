import React, { useState, useEffect } from 'react'
import './../../assets/css/react-paginate.css'
import { CButton, CCol, CForm, CFormInput, CFormSelect } from '@coreui/react'
import { useFormik } from 'formik'
import { ToastContainer, toast } from 'react-toastify'
import { jwtDecode } from 'jwt-decode'
import {
  DefaultLoading,
  RequiredFieldNote,
  api,
  decrypted,
  requiredField,
  toSentenceCase,
} from 'src/components/SystemConfiguration'
import * as Yup from 'yup'

const UserProfile = () => {
  const [validated, setValidated] = useState(true)
  const [operationLoading, setOperationLoading] = useState(false)
  const [editId, setEditId] = useState('')

  useEffect(() => {
    const userInfo = jwtDecode(localStorage.getItem('scholarshipToken'))
    setEditId(userInfo.id)
    api
      .get('user/find/' + userInfo.id)
      .then((response) => {
        const res = decrypted(response.data)
        form.setValues({
          first_name: res.firstname,
          middle_name: res.middlename,
          last_name: res.lastname,
          username: res.username,
          role_type: res.role,
        })
      })
      .catch((error) => {
        toast.error('Error fetching data')
      })
  }, [])

  const validationSchema = Yup.object().shape({
    first_name: Yup.string().required('First Name is required'),
    last_name: Yup.string().required('Last Name is required'),
    username: Yup.string().required('Username is required'),
    role_type: Yup.string().required('Role Type is required'),
  })

  const form = useFormik({
    initialValues: {
      first_name: '',
      last_name: '',
      middle_name: '',
      username: '',
      role_type: '',
    },
    validationSchema: validationSchema,
    onSubmit: async (values) => {
      setOperationLoading(true)
      await api
        .put('user/update/' + editId, values)
        .then((response) => {
          toast.success(response.data.message)
          setValidated(false)
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
    if (type === 'text' && name !== 'username') {
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
          <CFormInput
            type="text"
            feedbackInvalid="First Name is required."
            label={requiredField('First Name')}
            name="first_name"
            onChange={handleInputChange}
            value={form.values.first_name}
            required
            placeholder="First Name"
          />
          <CFormInput
            type="text"
            label="Middle Initial"
            name="middle_name"
            onChange={handleInputChange}
            value={form.values.middle_name}
            placeholder="Middle Initial"
          />
          <CFormInput
            type="text"
            feedbackInvalid="Last Name is required."
            label={requiredField('Last Name')}
            name="last_name"
            onChange={handleInputChange}
            value={form.values.last_name}
            required
            placeholder="Last Name"
          />
          <CFormInput
            type="text"
            feedbackInvalid="Username is required."
            label={requiredField('Username')}
            name="username"
            onChange={handleInputChange}
            value={form.values.username}
            required
            placeholder="Username"
          />
          <CFormSelect
            aria-label="Role Type"
            feedbackInvalid="Role Type is required."
            label={requiredField('Role Type')}
            name="role_type"
            onChange={handleInputChange}
            value={form.values.role_type}
            required
          >
            <option value="">Select</option>
            <option value="Administrator">Administrator</option>
            <option value="Encoder">Encoder</option>
            <option value="User">User</option>
          </CFormSelect>
        </CCol>

        <hr />
        <CCol xs={12}>
          <CButton color="primary" type="submit" className="float-end">
            Update Profile
          </CButton>
        </CCol>
      </CForm>
      {operationLoading && <DefaultLoading />}
    </>
  )
}

export default UserProfile
